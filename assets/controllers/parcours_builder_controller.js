import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        saveUrl: String,
        token: String,
        state: Object,
    };

    static targets = ['modules', 'coursList', 'roadmap', 'statut', 'objectif', 'notes', 'form', 'moduleOrderField', 'coursOrderField', 'coursMetaField'];

    connect() {
        this._initialized = false;
        this._initAttempts = 0;
        this._sortableLoading = false;

        if (typeof console !== 'undefined' && console && typeof console.debug === 'function') {
            console.debug('[parcours-builder] connect');
        }

        this._initWhenReady();

        window.setTimeout(() => {
            if (!this._initialized) {
                this._showInitError('Le drag & drop n\'est pas initialisé. Vérifie que les scripts JS sont chargés (importmap + SortableJS).');
            }
        }, 2000);
    }

    _initWhenReady() {
        if (this._initialized) {
            return;
        }

        this._initAttempts += 1;

        if (typeof window.Sortable === 'undefined' || typeof window.Sortable.create !== 'function') {
            if (!this._sortableLoading) {
                this._sortableLoading = true;
                this._loadSortableFromCdn();
            }

            if (this._initAttempts < 200) {
                window.setTimeout(() => this._initWhenReady(), 50);
            }
            return;
        }

        this._initialized = true;
        this.restoreMetaFromState();
        this.bindMetaListeners();
        this.initModuleSortable();
        this.initCoursSortables();
        this.renderRoadmap();
    }

    _loadSortableFromCdn() {
        if (document.querySelector('script[data-sortablejs="1"]')) {
            return;
        }

        const script = document.createElement('script');
        script.setAttribute('data-sortablejs', '1');
        script.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js';
        script.async = true;
        script.onload = () => {
            if (typeof console !== 'undefined' && console && typeof console.debug === 'function') {
                console.debug('[parcours-builder] SortableJS loaded');
            }
        };
        script.onerror = () => {
            this._showInitError('Impossible de charger SortableJS. Vérifie ta connexion internet ou le chargement des scripts.');
        };
        document.head.appendChild(script);
    }

    _showInitError(message) {
        if (this._initialized) {
            return;
        }

        const target = this.hasRoadmapTarget ? this.roadmapTarget : null;
        if (!target) {
            return;
        }

        target.innerHTML = '';
        const box = document.createElement('div');
        box.className = 'p-4 rounded-xl border border-border bg-muted/10 text-sm text-muted-foreground';
        box.textContent = message;
        target.appendChild(box);
    }

    bindMetaListeners() {
        if (this._metaListenersBound) {
            return;
        }
        this._metaListenersBound = true;

        this.statutTargets.forEach((el) => {
            el.addEventListener('change', () => this.renderRoadmap());
        });

        // Les champs objectif/notes ne changent pas la roadmap visuelle pour l'instant,
        // mais on peut réagir à l'input si tu veux afficher un indicateur plus tard.
    }

    disconnect() {
        if (this.modulesSortable && typeof this.modulesSortable.destroy === 'function') {
            this.modulesSortable.destroy();
        }
        if (Array.isArray(this.coursSortables)) {
            this.coursSortables.forEach((s) => {
                if (s && typeof s.destroy === 'function') {
                    s.destroy();
                }
            });
        }
        this._initialized = false;
    }

    restoreMetaFromState() {
        if (!this.hasStateValue || !this.stateValue || typeof this.stateValue !== 'object') {
            return;
        }

        const meta = this.stateValue.coursMeta && typeof this.stateValue.coursMeta === 'object' ? this.stateValue.coursMeta : {};

        this.statutTargets.forEach((el) => {
            const id = parseInt(el.getAttribute('data-cours-id'), 10);
            if (!Number.isFinite(id)) {
                return;
            }
            const m = meta[id] || meta[String(id)];
            if (m && typeof m === 'object' && typeof m.statut === 'string') {
                el.value = m.statut;
            }
        });

        this.objectifTargets.forEach((el) => {
            const id = parseInt(el.getAttribute('data-cours-id'), 10);
            if (!Number.isFinite(id)) {
                return;
            }
            const m = meta[id] || meta[String(id)];
            if (m && typeof m === 'object' && typeof m.objectif === 'string') {
                el.value = m.objectif;
            }
        });

        this.notesTargets.forEach((el) => {
            const id = parseInt(el.getAttribute('data-cours-id'), 10);
            if (!Number.isFinite(id)) {
                return;
            }
            const m = meta[id] || meta[String(id)];
            if (m && typeof m === 'object' && typeof m.notes === 'string') {
                el.value = m.notes;
            }
        });
    }

    initModuleSortable() {
        if (!this.hasModulesTarget) {
            return;
        }

        this.modulesSortable = window.Sortable.create(this.modulesTarget, {
            animation: 150,
            handle: '.parcours-handle-module',
            draggable: '[data-parcours-module][data-module-id]',
            onEnd: () => this.renderRoadmap(),
        });
    }

    initCoursSortables() {
        this.coursSortables = [];

        this.coursListTargets.forEach((el) => {
            const sortable = window.Sortable.create(el, {
                animation: 150,
                handle: '.parcours-handle-cours',
                draggable: '[data-cours-id]',
                group: {
                    name: 'cours',
                    pull: true,
                    put: true,
                },
                onEnd: () => this.renderRoadmap(),
            });

            this.coursSortables.push(sortable);
        });
    }

    buildState() {
        const moduleOrder = [];
        const coursOrder = {};
        const coursMeta = {};

        if (!this.hasModulesTarget) {
            return { moduleOrder, coursOrder, coursMeta };
        }

        this.modulesTarget.querySelectorAll('[data-parcours-module][data-module-id]').forEach((moduleEl) => {
            const moduleId = parseInt(moduleEl.getAttribute('data-module-id'), 10);
            if (!Number.isFinite(moduleId)) {
                return;
            }

            moduleOrder.push(moduleId);

            const coursList = moduleEl.querySelector('[data-module-id][data-parcours-builder-target="coursList"], [data-parcours-builder-target="coursList"]');
            const items = [];
            if (coursList) {
                coursList.querySelectorAll('[data-parcours-cours][data-cours-id]').forEach((coursEl) => {
                    const cid = parseInt(coursEl.getAttribute('data-cours-id'), 10);
                    if (Number.isFinite(cid)) {
                        items.push(cid);
                    }
                });
            }

            coursOrder[moduleId] = items;
        });

        this.statutTargets.forEach((el) => {
            const id = parseInt(el.getAttribute('data-cours-id'), 10);
            if (!Number.isFinite(id)) {
                return;
            }

            const statut = typeof el.value === 'string' && el.value !== '' ? el.value : 'todo';
            coursMeta[id] = { ...(coursMeta[id] || {}), statut };
        });

        this.objectifTargets.forEach((el) => {
            const id = parseInt(el.getAttribute('data-cours-id'), 10);
            if (!Number.isFinite(id)) {
                return;
            }

            const objectif = typeof el.value === 'string' ? el.value : '';
            coursMeta[id] = { ...(coursMeta[id] || {}), objectif };
        });

        this.notesTargets.forEach((el) => {
            const id = parseInt(el.getAttribute('data-cours-id'), 10);
            if (!Number.isFinite(id)) {
                return;
            }

            const notes = typeof el.value === 'string' ? el.value : '';
            coursMeta[id] = { ...(coursMeta[id] || {}), notes };
        });

        return { moduleOrder, coursOrder, coursMeta };
    }

    buildTitleMaps() {
        const moduleTitles = {};
        const coursTitles = {};

        if (!this.hasModulesTarget) {
            return { moduleTitles, coursTitles };
        }

        this.modulesTarget.querySelectorAll('[data-parcours-module][data-module-id]').forEach((moduleEl) => {
            const moduleId = parseInt(moduleEl.getAttribute('data-module-id'), 10);
            if (!Number.isFinite(moduleId)) {
                return;
            }

            const moduleTitre = moduleEl.getAttribute('data-module-titre');
            if (moduleTitre) {
                moduleTitles[moduleId] = moduleTitre;
            }

            moduleEl.querySelectorAll('[data-parcours-cours][data-cours-id]').forEach((coursEl) => {
                const cid = parseInt(coursEl.getAttribute('data-cours-id'), 10);
                if (!Number.isFinite(cid)) {
                    return;
                }

                const coursTitre = coursEl.getAttribute('data-cours-titre');
                if (coursTitre) {
                    coursTitles[cid] = coursTitre;
                }
            });
        });

        return { moduleTitles, coursTitles };
    }

    prepareSubmit(event) {
        const { moduleOrder, coursOrder, coursMeta } = this.buildState();

        if (this.hasModuleOrderFieldTarget) {
            this.moduleOrderFieldTarget.value = JSON.stringify(moduleOrder);
        }
        if (this.hasCoursOrderFieldTarget) {
            this.coursOrderFieldTarget.value = JSON.stringify(coursOrder);
        }
        if (this.hasCoursMetaFieldTarget) {
            this.coursMetaFieldTarget.value = JSON.stringify(coursMeta);
        }

        if (this.hasTokenValue) {
            // token envoyé via <input name="_token"> côté Twig
        }

        return true;
    }

    renderRoadmap() {
        if (!this.hasRoadmapTarget) {
            return;
        }

        const { moduleOrder, coursOrder, coursMeta } = this.buildState();
        const { moduleTitles, coursTitles } = this.buildTitleMaps();
        this.roadmapTarget.innerHTML = '';

        if (moduleOrder.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'p-4 rounded-xl border border-border bg-muted/10 text-sm text-muted-foreground';
            empty.textContent = 'Aucun module dans le parcours.';
            this.roadmapTarget.appendChild(empty);
            return;
        }

        moduleOrder.forEach((mid) => {
            const moduleCard = document.createElement('div');
            moduleCard.className = 'p-4 rounded-xl border border-border bg-white';

            const title = document.createElement('div');
            title.className = 'font-semibold mb-2';
            title.textContent = moduleTitles[mid] ? moduleTitles[mid] : 'Module #' + mid;
            moduleCard.appendChild(title);

            const items = Array.isArray(coursOrder[mid]) ? coursOrder[mid] : [];
            const total = items.length;
            const done = items.filter((cid) => coursMeta[cid] && coursMeta[cid].statut === 'done').length;
            const percent = total > 0 ? Math.round((done / total) * 100) : 0;

            const progressWrap = document.createElement('div');
            progressWrap.className = 'mb-3';

            const progressLabel = document.createElement('div');
            progressLabel.className = 'text-xs text-muted-foreground mb-1';
            progressLabel.textContent = total > 0 ? `Progression: ${done}/${total} (${percent}%)` : 'Progression: -';
            progressWrap.appendChild(progressLabel);

            const progressBar = document.createElement('div');
            progressBar.className = 'h-2 rounded-full bg-muted overflow-hidden border border-border';
            const progressFill = document.createElement('div');
            progressFill.className = 'h-full bg-primary';
            progressFill.style.width = percent + '%';
            progressBar.appendChild(progressFill);
            progressWrap.appendChild(progressBar);
            moduleCard.appendChild(progressWrap);

            const list = document.createElement('div');
            list.className = 'space-y-1';
            if (items.length === 0) {
                const li = document.createElement('div');
                li.className = 'text-sm text-muted-foreground';
                li.textContent = 'Aucun cours.';
                list.appendChild(li);
            } else {
                items.forEach((cid, idx) => {
                    const li = document.createElement('div');
                    li.className = 'text-sm';
                    const label = coursTitles[cid] ? coursTitles[cid] : 'Cours #' + cid;

                    const statut = coursMeta[cid] && typeof coursMeta[cid].statut === 'string' ? coursMeta[cid].statut : 'todo';
                    const statutLabel = statut === 'done' ? 'Terminé' : (statut === 'doing' ? 'En cours' : 'À faire');

                    li.textContent = (idx + 1) + '. ' + label + ' — ' + statutLabel;
                    list.appendChild(li);
                });
            }

            moduleCard.appendChild(list);
            this.roadmapTarget.appendChild(moduleCard);
        });
    }
}
