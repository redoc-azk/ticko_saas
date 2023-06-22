import { Controller } from '@hotwired/stimulus';
import * as Turbolinks from "@hotwired/turbo";

export default class extends Controller {
    static targets = ['source'];
    initialize() {
        super.initialize();
    }

    connect() {}

    search(event) {
        // submit #search-form form
        event.preventDefault();
        const form = document.getElementById('search-form');
        form.submit();
    }

}
