<?php

namespace App\Models;

use Database\Factories\LeadDownloadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Uma linha do historico do lead: um material baixado, um envio. */
class LeadDownload extends Model
{
    /** @use HasFactory<LeadDownloadFactory> */
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'submission_id',
        'origem',
        'media_id',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }
}
