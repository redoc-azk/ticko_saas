import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    initialize() {
        super.initialize();
        // check if window.Messenger is defined and window.$
        if (window.Messenger && window.$) {
            $(function(){
                var loc = ['bottom', 'right'];
                var style = 'flat';

                var $output = $('.controls output');
                var $lsel = $('.location-selector');
                var $tsel = $('.theme-selector');

                var update = function(){
                    var classes = 'messenger-fixed';

                    for (var i=0; i < loc.length; i++)
                        classes += ' messenger-on-' + loc[i];

                    $.globalMessenger({ extraClasses: classes, theme: style });
                    Messenger.options = { extraClasses: classes, theme: style };

                    $output.text("Messenger.options = {\n    extraClasses: '" + classes + "',\n    theme: '" + style + "'\n}");
                };

                update();

                $lsel.locationSelector()
                    .on('update', function(pos){
                        loc = pos;

                        update();
                    })
                ;

                $tsel.themeSelector({
                    themes: ['flat', 'future', 'block', 'air', 'ice']
                }).on('update', function(theme){
                    style = theme;

                    update();
                });

            });
        }
    }

    connect() {
    }

    scanParticipant(){
        console.log("ewfwe")
        const pId = localStorage.getItem('participantId');
        // fetch on get /app/list/participant/confirm/{id} if status is "success" then alert "Participant scanné avec succès" if error alert "Erreur lors du scan"
        fetch(`/app/list/participant/confirm/${pId}`)
            .then(response => response.json())
            .then(data => {
                if(data.status === "success"){
                    //alert("Participant scanné avec succès");
                    // if window.Messenger is defined, post a message to it
                    if (window.Messenger) {
                        window.Messenger().post('Participant scanné avec succès');
                    }else{
                        alert("Participant scanné avec succès");
                    }
                }else{
                    alert("Erreur lors du scan");
                }
            })
        // simulate a click on #modal_close_btn to close the modal
        document.getElementById('modal_close_btn').click();
    }
}
