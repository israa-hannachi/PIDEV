import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String,
        token: String,
        moduleId: Number,
    };

    static targets = ['contenu', 'resume'];

    async generate(event) {
        event.preventDefault();

        const contenu = this.contenuTarget?.value || '';
        if (!contenu.trim()) {
            alert('Veuillez saisir le contenu du cours avant de générer un résumé.');
            return;
        }

        let moduleId = this.moduleIdValue || 0;
        const moduleField = this.element.querySelector('select[name$="[module]"]');
        if (moduleField && moduleField.value) {
            moduleId = parseInt(moduleField.value, 10) || moduleId;
        }

        const btn = event.currentTarget;
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
                    contenu,
                    moduleId,
                }),
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                const message = data && data.error ? data.error : 'Erreur lors de la génération du résumé.';
                throw new Error(message);
            }

            if (data && typeof data.summary === 'string') {
                this.resumeTarget.value = data.summary;
                this.resumeTarget.dispatchEvent(new Event('change', { bubbles: true }));
            }
        } catch (e) {
            alert(e && e.message ? e.message : 'Erreur lors de la génération du résumé.');
        } finally {
            btn.disabled = false;
            btn.textContent = oldText;
        }
    }
}
