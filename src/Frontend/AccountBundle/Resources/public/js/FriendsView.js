/*
 * A barátok oldalhoz szükséges funkciók vannak ebben az objektumban
 */
FriendsView = {
    
    init: function(){
        console.log('FriendsView init');
        this.bindUiActions();
    },
    
    bindUiActions: function(){
        this.addQtips();
        this.bindAcceptFriendButton();
    },
    
    /*
     * A barát dobozokban lévő 'x'-ekhez, buborék hozzáadása(kattintásra)
     */
    addQtips: function(){
        $('.friends .friend-object .delete-holder').each(function(){
            var friendObject = $(this).parents('.friend-object');
            if(friendObject.hasClass('qtip-added')) return;
            
            friendObject.addClass('qtip-added');
            var userId = friendObject.attr('userid');
            var html = $('.qtips-content.'+$(this).attr('qtip-content')).find('.deleteThisFriend').attr('userid', userId).end().html();
            var $this = $(this);
            
            $this.qtip({
                content: {
                        text: html
                    },
                    show: 'click',
                    hide: {
                         event: 'click'
                    },    
                position: {
                       my: 'left top',  // Position my top left...
                       at: 'right center', // at the bottom right of...
                       target:$this// my target
                   }, 
                style: {
                    def: false,
                    classes: "qtip-custom-black"+" "+Shared.qtipStyleClass2,
            },
            events: {
                render: function(){
                    FriendsView.bindHideQtipButton();
                    FriendsView.bindDeleteFriendButton();
                }
            }
            });
        });
        
    },
    
    /*
     * A buborékok bezárás gombjának felüldefiniálása
     */
    bindHideQtipButton: function(){
        $('.hideQtip').unbind('click')
                .bind('click', function(){
                    $(this).parents('.qtip-focus').qtip('hide');
                 });
    },
    
    /*
     * A barát törlése gomb eseményének felüldefiniálása
     */
    bindDeleteFriendButton: function(){
        $('.deleteThisFriend').unbind('click')
                .bind('click', function(){
                    var userId = $(this).attr('userid');
                    $(this).parents('.qtip-focus').qtip('hide');
                    var div = $('.friend-object[userid="'+userId+'"]');
            
                    div.effect( 'transfer', { to: ".headerHolder .leftHolder a", className: "transferAnimation" }, 500,callback);
                    function callback() {
                      div.removeAttr( "style" ).hide();
                    };
                
                    FriendsView.post(userId, 'reject');
                 });
    },
    
    /*
     * a barátnak visszajelölés gomb eseményének felüldefiniálása
     */
    bindAcceptFriendButton: function(){
      $('.friend-object .accept-friend')
              .unbind('click')
              .bind('click', function(){
                var div = $(this).parents('.friend-object').first();
                var userId = div.attr('userid');
                var options = {};
                
                var divClone = div.clone();
                divClone.find('.accept-friend').remove();
                divClone.removeClass('qtip-added');
                divClone.find('.delete-holder').attr('qtip-content', 'my-friend');
                divClone.appendTo( ".universal-tabs-content .tab.friends");
                FriendsView.addQtips();
               
                options = { to: ".universal-tabs .universal-tabs-header li:first", className: "transferAnimation" };
              
                div.effect( 'transfer', options, 500,callback);
                function callback() {
                  div.removeAttr( "style" ).hide();
                };
                
                FriendsView.post(userId, 'accept');
              });
    },
    
    //adatok küldése a szervernek
    post: function (userId, type){ 
        $.post(FriendsShared.addOrRemoveURL, {
              userID : userId,
              type : type
        }).done(function(d){
           if(d.err){
               alert('hiba : '+d.err);
           }else{
               console.log('sikeres mentés');
           }
        });
    },
    
}