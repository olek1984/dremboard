jQuery( document ).ready( function(){
  
    //let us dome some magic
    
    var jq = jQuery;
      
    function bind_editable_activity(){
       
        jq( 'body' ).editable({
          
            type: 'textarea',
            name: 'activity-content',  
            pk: function(){
                    //activity id
                    return jq(this).data('id') ;

            },
            selector: '.acomment-edit',//delegated to it
            url: ajaxurl,
            params: function( params ){
                //for wp action
               params.action = 'editable_activity_update';
               params.nonce = jq('#_activity_edit_nonce_'+params.pk).val()
               return params;
            },
            display: function (value, sourceData){
                    //just don't let xeditable do it for us
            },

            success: function( response, newValue ) {
                if(response.error) 
                    return response.message; //msg will be shown in editable form
                
                if( response.success){
                    //update content
                    jq( '#activity-' + response.data.id ).replaceWith( response.data.content );
                           
                }
            },
            title: BPEditableActivity.edit_activity_title
    });
  
  }
   
    bind_editable_activity();
    
    
    //for activity comment
    function bind_editable_activity_comment(){
    
        jq( 'body').editable({
          
        type: 'textarea',
        name: 'activity-text',  
        pk: function(){
                //activity comment id
                return jq(this).data('id') ;

        },
        selector: '.acomment-reply-edit',
        url: ajaxurl,
        params: function( params ){
           params.action = 'editable_activity_comment_update';
           params.nonce = jq('#_activity_edit_nonce_' + params.pk ).val()
           return params;
        },
        display: function (value, sourceData){

        },

        success: function( response, newValue ) {
            
            if( response.error )
                return response.message; //msg will be shown in editable form
            
            if( response.success ){
                //update content

                jq( '#acomment-' + response.data.id ).replaceWith( response.data.content );
               
            }
        },
        title: BPEditableActivity.edit_comment_title
       });

    }
    bind_editable_activity_comment();



});