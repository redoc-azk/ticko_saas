import { startStimulusApp } from '@symfony/stimulus-bridge';
import participantmodal from "./controllers/participantmodal";
import qr_controller from "./controllers/qr_controller";
import search_controller from "./controllers/search_controller";

// Registers Stimulus controllers from controllers.json and in the controllers/ directory
export const app = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.[jt]sx?$/
));
// register any custom, 3rd party controllers here
app.register('participantmodal', participantmodal);
app.register('qr', qr_controller);
app.register('search', search_controller);
