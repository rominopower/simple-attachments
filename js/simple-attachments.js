jQuery(function($) {
    $(document).ready(function() {
        var urlajax=variabili.urlajax;

        
        $(".sa-img").click(function() {
           var img=this.id;
               
                frame = wp.media({
                    library: {
                        type: 'image', post__in:[this.id]
                    },
                    multiple: false,
                    button: {
                        text: 'Allega'
                    },
                    allowLocalEdits: true,
                    displaySettings: true,
                    displayUserSettings: true,
                    
                });
            
            
       
          frame.on('open', function(){
        // alert(img);
            var selection = frame.state().get('selection');
            var selected = img; // the id of the image
        if (selected) {
        	  	
        selection.add(wp.media.attachment(selected));
        }
        });
          
          frame.on('select', function() {
        	
        	   var selected = img;
        	//   alert("clicco tasto"+img); 
        	   
        	  var selection = frame.state().get('selection');
        	  
        	/*  if(selection) {
        		  alert("è selezionato");
        	  } else {
        		  alert("non è selezionato");
        	  }
        	  */
        	  
          });
          
          
           frame.open();
            });
        
        
        
        
        
        $("#attach-media").click(function() {
          //  event.preventDefault();
            if (this.window === undefined) {
                this.window = wp.media({
                    title: 'Insert a media',
                    library: {
                        type: 'image'
                    },
                    multiple: true,
                    button: {
                        text: 'Allega'
                    }
                });

                var self = this; // Needed to retrieve our variable in the anonymous function below
                
                // quando viene cliccato il tasto "allega"
                this.window.on('select', function() {
               //     event.preventDefault();
                    //  var first = self.window.state().get('selection').first().toJSON();
                    var alls = self.window.state().get('selection');
                    var i = 0;


                    


                    var ids = alls.map(function(attachment) {
                        i++;
                        attach = attachment.toJSON();
                        // console.log(i + ' ' + attach.id);
                        // console.log(i + ' ' + attach.title);

                        return attach.id;
                    });
                    
                    // recupera il nome del postid a cui associare le immagini
                    postid=$("#post_ID").val();
                    
                   

                    jQuery.ajax({
                        type: 'POST',
                        url: urlajax,
                        data: {
                            action: 'myAjax',
                            // la variabile da inviare alla funzione via post
                            variabile: ids,
                            postid: postid,
                        },
                        success: function(data, textStatus, XMLHttpRequest) {
                            // per covertire dei dati json restituiti dalla funzione
                            //  var obj = jQuery.parseJSON(data);
                           // alert(data);
                            // qui metti cosa fare con la funzione in caso di risposta ricevuta
                         console.log(data);
                        // ricarica la pagina per fare vedere le modifiche
                        location.reload();
                        },
                        error: function(MLHttpRequest, textStatus, errorThrown) {
                            alert(errorThrown);
                            alert(urlajax);
                        }
                    });

                    //return false;
                    //  wp.media.editor.insert('[myshortcode id="' + first.id + '"]');
                });
            }
            // fine tasto allega    
            this.window.open();
            return false;
        //end click mymedia
        });
// end document ready
    });

});