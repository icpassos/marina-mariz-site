<?php

namespace App\Http\Controllers;

use App\Enums\StatusInscricao;
use App\Http\Requests\FormularioRequest;
use App\Http\Requests\LeadRequest;
use App\Http\Requests\MensagemRequest;
use App\Http\Requests\NewsletterRequest;
use App\Models\Consentimento;
use App\Models\InscricaoNewsletter;
use App\Models\Lead;
use App\Models\LeadDownload;
use App\Models\Media;
use App\Models\Mensagem;
use App\Support\Outbox;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Entradas de dado do site (doc 04).
 *
 * Em todos os casos: uma transacao grava o registro principal e o item da
 * outbox. O visitante recebe sucesso assim que o banco confirma; o e-mail de
 * aviso sai depois, pelo comando `outbox:processar`. Se o banco falhar, nada
 * e gravado e o visitante volta com os campos preenchidos.
 */
class FormulariosController extends Controller
{
    public function contato(MensagemRequest $request): Response
    {
        return $this->mensagem($request);
    }

    public function formacaoProfissional(MensagemRequest $request): Response
    {
        return $this->mensagem($request);
    }

    private function mensagem(MensagemRequest $request): Response
    {
        $mensagem = DB::transaction(function () use ($request): Mensagem {
            // firstOrCreate sobre submission_id unico: reenviar a mesma chave
            // devolve o mesmo registro, sem segunda mensagem.
            $mensagem = Mensagem::firstOrCreate(
                ['submission_id' => $request->submissionId()],
                [
                    'origem' => $request->origem(),
                    'nome' => $request->string('nome')->toString(),
                    'email' => $request->string('email')->toString(),
                    'whatsapp' => $request->input('whatsapp'),
                    'texto' => $request->string('texto')->toString(),
                    'profissao' => $request->input('profissao'),
                    'registro_profissional' => $request->input('registro_profissional'),
                    'instituicao' => $request->input('instituicao'),
                    'modalidade' => $request->input('modalidade'),
                    'politica_versao' => $request->politicaVersao(),
                    'ip_hash' => $request->ipHash(),
                ],
            );

            Outbox::enfileirar($mensagem, $request->submissionId());

            return $mensagem;
        });

        return $this->sucesso($request, $mensagem);
    }

    public function material(LeadRequest $request): Response
    {
        $material = Media::findOrFail($request->integer('media_id'));

        $lead = DB::transaction(function () use ($request, $material): Lead {
            // Mesmo e-mail = um lead so, com historico crescente (doc 04).
            $lead = Lead::firstOrCreate(
                ['email' => $request->string('email')->toString()],
                [
                    'nome' => $request->string('nome')->toString(),
                    'telefone' => $request->input('telefone'),
                    'politica_versao' => $request->politicaVersao(),
                    'ip_hash' => $request->ipHash(),
                ],
            );

            LeadDownload::firstOrCreate(
                ['submission_id' => $request->submissionId()],
                [
                    'lead_id' => $lead->getKey(),
                    'origem' => $material->name,
                    'media_id' => $material->getKey(),
                ],
            );

            if ($request->boolean('aceita_newsletter')) {
                $this->registrarConsentimento($lead, Consentimento::NEWSLETTER, $request);
            }

            Outbox::enfileirar($lead, $request->submissionId());

            return $lead;
        });

        // Entrega imediata na propria sessao: o link de 3 dias aparece na tela
        // e o e-mail e conveniencia, nao condicao para receber o arquivo.
        return $this->sucesso($request, $lead, DownloadProtegidoController::linkPara($material));
    }

    public function newsletter(NewsletterRequest $request): Response
    {
        $inscricao = DB::transaction(function () use ($request): InscricaoNewsletter {
            $inscricao = InscricaoNewsletter::firstOrCreate(
                ['email' => $request->string('email')->toString()],
                [
                    'nome' => $request->input('nome'),
                    'origem' => $request->string('origem')->toString(),
                    'status' => StatusInscricao::Ativo,
                    'submission_id' => $request->submissionId(),
                    'descadastro_token' => InscricaoNewsletter::novoToken(),
                    'politica_versao' => $request->politicaVersao(),
                    'ip_hash' => $request->ipHash(),
                ],
            );

            // Quem voltou depois de sair reativa e ganha token novo.
            if ($inscricao->status === StatusInscricao::Descadastrado) {
                $inscricao->forceFill([
                    'status' => StatusInscricao::Ativo,
                    'descadastro_token' => InscricaoNewsletter::novoToken(),
                    'descadastrado_em' => null,
                ])->save();
            }

            $this->registrarConsentimento($inscricao, Consentimento::NEWSLETTER, $request);

            Outbox::enfileirar($inscricao, $request->submissionId());

            return $inscricao;
        });

        return $this->sucesso($request, $inscricao);
    }

    /**
     * Descadastro de um clique (doc 04). Token de uso unico: some do banco ao
     * ser usado, entao o mesmo link nao serve duas vezes.
     */
    public function descadastrar(string $token): View
    {
        $inscricao = InscricaoNewsletter::where('descadastro_token', $token)->first();

        $inscricao?->descadastrar();

        return view('pessoas.descadastro', [
            'descadastrada' => $inscricao !== null,
        ]);
    }

    /** Consentimento: finalidade, versao do aviso, data/hora e IP pseudonimizado. */
    private function registrarConsentimento(Model $registro, string $finalidade, FormularioRequest $request): void
    {
        $registro->consentimentos()->create([
            'finalidade' => $finalidade,
            'versao_aviso' => $request->politicaVersao(),
            'ip_hash' => $request->ipHash(),
            'concedido_em' => now(),
        ]);
    }

    /**
     * O banco confirmou: sucesso imediato. JSON para o formulario com JS,
     * volta com flash para o envio sem JS.
     */
    private function sucesso(FormularioRequest $request, Model $registro, ?string $linkDeDownload = null): Response
    {
        $dados = array_filter([
            'ok' => true,
            'submission_id' => $request->submissionId(),
            'download_url' => $linkDeDownload,
            // Qual formulario da pagina respondeu. Uma pagina tem mais de um;
            // sem isso o recibo apareceria no formulario vizinho.
            'form' => $request->input('form'),
        ]) + [
            // Falso quando o registro ja existia: e o que separa "Pronto!"
            // de "Voce ja esta na lista" na tela, sem consultar o banco de novo.
            'novo' => $registro->wasRecentlyCreated,
        ];

        if ($request->expectsJson()) {
            return response()->json($dados);
        }

        return redirect()->back()->with($dados);
    }
}
