<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Notifications\Notification;

class DocumentAnalysisComplete extends Notification
{
    public function __construct(
        public readonly Document $document,
        public readonly bool $success = true,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        if ($this->success) {
            return [
                'type'    => 'document_analysis_complete',
                'title'   => 'Análise concluída',
                'message' => "O documento \"{$this->document->title}\" foi analisado pela IA.",
                'url'     => route('documents.show', $this->document->id),
            ];
        }

        return [
            'type'    => 'document_analysis_error',
            'title'   => 'Falha na análise',
            'message' => "Não foi possível analisar \"{$this->document->title}\". Tente novamente.",
            'url'     => route('documents.show', $this->document->id),
        ];
    }
}
