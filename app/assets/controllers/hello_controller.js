import { Controller } from '@hotwired/stimulus';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
    static targets = [ "source" ]
    initialize() {
        super.initialize();
        this.pid = null;
    }

    connect() {
    }

    greet() {
        // set pid from this.element data-id attr
        this.pid = this.sourceTarget.dataset.id;
        localStorage.setItem("participantId", this.pid);

        document.querySelector("#scan_btn").style.display = 'none';
        // /app/list/participant/{id} on get and console log
        fetch('/app/list/participant/' + this.pid)
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                // set val nom_prenoms
                document.querySelector('#nom_prenoms').innerHTML = data.data.nomPrenoms;
                document.querySelector('#indicatif_telephonique').innerHTML = data.data.indicatifTelephonique;
                document.querySelector('#numero_telephone').innerHTML = data.data.numero;
                document.querySelector('#genre').innerHTML = data.data.sexe;
                document.querySelector('#fonction').innerHTML = data.data.profession;
                document.querySelector('#entreprise').innerHTML = data.data.entreprise;
                document.querySelector('#ville').innerHTML = data.data.ville;
                document.querySelector('#pays').innerHTML = data.data.pays;
                document.querySelector('#inscrit_le').innerHTML = data.data.createdAt;
                // if scannedAt is not null, in div #scanned_div show scannedAt else write not scanned
                if (data.data.scannedAt !== null) {
                    // scanned div is not hidden per default, set innerhtml
                    document.querySelector('#scanned_div').innerHTML =
                        '<span class="label label-success">Scanné le ' + data.data.scannedAt + '</span>';
                }else{
                    // scanned div is not hidden per default, set innerhtml
                    document.querySelector('#scanned_div').innerHTML =
                        '<span class="label label-important">Non scanné</span>';
                    document.querySelector('#scan_btn').style.display = 'inline';
                }
            });
    }
}
