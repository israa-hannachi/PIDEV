import { startStimulusApp } from '@symfony/stimulus-bundle';

const app = startStimulusApp();
// register any custom, 3rd party controllers here
import ParcoursBuilderController from './controllers/parcours_builder_controller.js';
app.register('parcours-builder', ParcoursBuilderController);

import AiSummaryController from './controllers/ai_summary_controller.js';
app.register('ai-summary', AiSummaryController);

import AiSummaryDisplayController from './controllers/ai_summary_display_controller.js';
app.register('ai-summary-display', AiSummaryDisplayController);
