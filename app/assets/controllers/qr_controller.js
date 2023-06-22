import { Controller } from '@hotwired/stimulus';
import {Html5QrcodeScanner} from "html5-qrcode";

// load sound from /assets/bip.mp3
const bip = new Audio('/assets/bip.mp3');

export default class extends Controller {
    initialize() {
        this.scanner = null;
    }

    connect() {
        try{
            // check if hasCamera
            //QrScanner.hasCamera().then(hasCamera => alert(`hasCamera: ${hasCamera}`));
            const _checkUuid = this.checkUuid;
            // if QrScanner object is defined
            const videoElem = document.getElementById('qr_scan_part');
            let html5QrcodeScanner = new Html5QrcodeScanner(
                "qr_scan_part",
                { fps: 10, qrbox: {width: 800, height: 800} },
                /* verbose= */ false);
            html5QrcodeScanner.render(
                function(decodedText, decodedResult){
                    // if scan code is a uuid v4 then
                    if(_checkUuid(decodedText)){
                        // stop scanning.
                        html5QrcodeScanner.pause();
                        // /app/scanner/{scanCode} if status is 200 then alert success else alert error
                        fetch('/app/scanner/' + decodedText)
                            .then(response => {
                                if(response.status === 200){
                                    // play sound success
                                    bip.play();
                                    // redirect to /app/list/participant/{id}
                                    window.location.href = '/app/list/participant/infos/' + decodedText;
                                }else{
                                    alert('Error Invalid QR Code');
                                }
                                html5QrcodeScanner.resume();
                            });
                    }
                }, function(){});
        }catch (e) {
            alert(e);
        }
    }

    checkUuid(input) {
        // if input is a uuid v4 then return true else return false
        if(input.match(/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/)){
            return true;
        }else{
            return false;
        }
    }

}
