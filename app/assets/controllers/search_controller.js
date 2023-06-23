import { Controller } from '@hotwired/stimulus';
import throttle from 'lodash.throttle'

export default class extends Controller {
    static targets = ['source', 'page'];
    initialize() {
        super.initialize();
        this.submit = throttle(this.submit, 100)
    }

    connect() {}

    search(event) {
        // /app/list/participants?page=1&term=p
        /*
         submit #search-form form
         */
        console.log('search');
    }

    submit() {
        this.element.requestSubmit();
    }

    popo(){
        console.log('popo');
    }
}
