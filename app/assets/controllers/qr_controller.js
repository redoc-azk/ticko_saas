import { Controller } from '@hotwired/stimulus';
import {Html5QrcodeScanner} from "html5-qrcode";


export default class extends Controller {
    initialize() {

    }

    connect() {
        try{
            // check if hasCamera
            //QrScanner.hasCamera().then(hasCamera => alert(`hasCamera: ${hasCamera}`));
            console.log('qr_controller connected');
            // if QrScanner object is defined
            const videoElem = document.getElementById('qr_scan_part');
            let html5QrcodeScanner = new Html5QrcodeScanner(
                "qr_scan_part",
                { fps: 10, qrbox: {width: 800, height: 800} },
                /* verbose= */ false);
            html5QrcodeScanner.render(this.onScanSuccess, this.onScanFailure);
        }catch (e) {
            alert(e);
        }
    }

    onScanSuccess(decodedText, decodedResult) {
        // handle the scanned code as you like, for example:
        // /app/scanner/{scanCode} if status is 200 then alert success else alert error
        fetch('/app/scanner/' + decodedText)
            .then(response => {
                if(response.status === 200){
                    alert('Success');
                }else{
                    alert('Error');
                }
            })
            .catch(error => {
                alert('Error');
            });
    }

    onScanFailure(error) {
        // handle scan failure, usually better to ignore and keep scanning.
        // for example:
        console.warn(`Code scan error = ${error}`);
    }

}
