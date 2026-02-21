import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String,
        pdfUrl: String,
        token: String,
        pdfToken: String,
    };

    static targets = ['output', 'button', 'textarea', 'download'];

    async generate(event) {
        event.preventDefault();

        const btn = this.hasButtonTarget ? this.buttonTarget : event.currentTarget;
        const oldText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Génération...';

        try {
            const response = await fetch(this.urlValue, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    _token: this.tokenValue,
                }),
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                const message = data && data.error ? data.error : 'Erreur lors de la génération du résumé.';
                throw new Error(message);
            }

            if (data && typeof data.summary === 'string') {
                if (this.hasTextareaTarget) {
                    this.textareaTarget.value = data.summary;
                    this.textareaTarget.classList.remove('hidden');
                }
                if (this.hasDownloadTarget) {
                    this.downloadTarget.classList.remove('hidden');
                }
            }
        } catch (e) {
            alert(e && e.message ? e.message : 'Erreur lors de la génération du résumé.');
        } finally {
            btn.disabled = false;
            btn.textContent = oldText;
        }
    }

    async downloadPdf(event) {
        event.preventDefault();

        if (!this.hasTextareaTarget) {
            alert('Résumé introuvable.');
            return;
        }

        const text = (this.textareaTarget.value || '').trim();
        if (!text) {
            alert('Le résumé est vide.');
            return;
        }

        const url = this.pdfUrlValue;
        if (!url) {
            alert('Téléchargement PDF indisponible.');
            return;
        }

        const btn = this.hasDownloadTarget ? this.downloadTarget : event.currentTarget;
        const oldText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Téléchargement...';

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/pdf',
                },
                body: JSON.stringify({
                    _token: this.pdfTokenValue,
                    text,
                }),
            });

            if (!response.ok) {
                const data = await response.json().catch(() => ({}));
                const message = data && data.error ? data.error : 'Erreur lors du téléchargement du PDF.';
                throw new Error(message);
            }

            const blob = await response.blob();
            const objectUrl = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = objectUrl;
            a.download = 'resume.pdf';
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(objectUrl);
        } catch (e) {
            alert(e && e.message ? e.message : 'Erreur lors du téléchargement du PDF.');
        } finally {
            btn.disabled = false;
            btn.textContent = oldText;
        }
    }
}
