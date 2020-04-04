let message = encodeURIComponent( $('#text_msg').val() );
let nbre_msg;

$("#messages").animate({ scrollTop: $("#messages").prop('scrollHeight')- $("#bloc_chat").prop('scrollHeight') }, 1000);
if(message != ""){ // on vérifie que les variables ne sont pas vides
    $.ajax({
        url : "php/chat_exe.php", // on donne l'URL du fichier de traitement
        type : "POST", // la requête est de type POST
        data : "message=" + message // et on envoie nos données
    });
}

$('#send_msg').click(function(e){

    let message = encodeURIComponent( $('#text_msg').val() );

    if(message != ""){ // on vérifie que les variables ne sont pas vides
        $.ajax({
            url : "php/chat_exe.php", // on donne l'URL du fichier de traitement
            type : "POST", // la requête est de type POST
            data : "message=" + message // et on envoie nos données
        });

        $('#messages').append('<div class="msg_out msg_remove"><p class="msg_name">'+$('#pseudo').text()+'</p><div class="msg"><p>'+$('#text_msg').val()+'</p></div></div>');
        $("#messages").animate({ scrollTop: $("#messages").prop('scrollHeight')- $("#bloc_chat").prop('scrollHeight') }, 300);
        $('#text_msg').val('');
    }
});

function charger(){
    nbre_msg=$('.bloc_msg').length;
    setTimeout( function(){

        let lastID = $('.bloc_msg:last').attr('data-id'); // on récupère l'id le plus récent
        console.log('tour');
        $.ajax({
            url : "php/chat_load.php?id=" + lastID, // on passe l'id le plus récent au fichier de chargement
            success : function(html){
                $('.msg_remove').remove();
                $('#messages').append(html);
            }
        });
        if(nbre_msg<$('.bloc_msg').length){
            console.log('BAM ! En voilà un en plus. SCROLLONS');
            $("#messages").animate({ scrollTop: $("#messages").prop('scrollHeight')- $("#bloc_chat").prop('scrollHeight') }, 1000);
        }
        else{
            console.log('nothing new');
        }

        charger();


    }, 3000);

}

charger();
