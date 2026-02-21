import { Application } from '@hotwired/stimulus';
import { definitionsFromContext } from '@symfony/stimulus-bundle';

const application = Application.start();

// Registers Stimulus controllers from controllers/*_controller.js
const context = require.context('./controllers', true, /_controller\.js$/);
application.load(definitionsFromContext(context));
