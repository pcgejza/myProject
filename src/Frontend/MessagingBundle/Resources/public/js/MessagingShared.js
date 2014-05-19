MessagingShared = {
    
    loadingDivHtml: '<div class="loading"></div>',
    
    getPageURL : null,
    PROGRESS: false,
    
    init: function(){
        
        this.bindUiActions();
        this.initSwitch();
        this.initNewMessageSend();
    },
    
    bindUiActions: function(){
        console.log('messagingShared.bindUi');
    },
    
    initSwitch: function(){
        $('.messages .messagesMenu li')
                .unbind('click')
                .bind('click', function(e){
                    e.preventDefault();
                    var a = $(this).find('a');
                    if(!$(this).hasClass('active')){
                        $(this).siblings('li').removeClass('active');
                        $(this).addClass('active');
                        MessagingShared.openPage(a.attr('page'), a.attr('getp'),a.attr('href'));
                    }
                });
    },
    
    initNewMessageSend: function(){
        $('.newMessageForm').submit(function(e){
            e.preventDefault();
            if(MessagingShared.PROGRESS){
                return; //Abban az esetben ha éppen zajlik a küldés , ne küldhesse el a user újra
            }
            var loadingE = $(this).find('.loading-mini');
            var err = false;
            var errorsDiv = $('.newMessage .errors');
            var resetButton = $(this).find('input[type="reset"]');
            
            // hibák kezelése (nicknévnek vagy jelszónak és üzenetnek muszály lennie
            var text = $(this).find('textarea.message');
            var nick = $(this).find('input.nick');
            if(nick.val().length == 0){
                err = "Adj meg egy nick nevet!";
            }
            if(text.val().length == 0){
                if(err){
                    err += "<br>Add meg az üzenetet!";
                }else{
                    err = "<br>Add meg az üzenetet!";
                }
            }
            if(err){
                errorsDiv.html(err); // hiba kiírása majd megáll a függvény működése
                return;
            }else{
                errorsDiv.html('');
            }
            
            // ha minden rendben, nem volt error akkor elkezdődik a küldés
            var postData = $(this).serializeArray();
            var formURL = $(this).attr("action");
            
            // loading megjelenítése
            loadingE.removeClass('hide');
            MessagingShared.PROGRESS = true;
            
            
            $.post(formURL, postData).done(function(data){
                loadingE.addClass('hide');
                MessagingShared.PROGRESS = false;
                
               if(data.err){
                   InfoPopUp.showInfoPopup({
                      topText : 'Az üzenet küldése sikertelen!',
                      text : data.err,
                      type : 'error'
                   });
               }else{
                   resetButton.click();
                   InfoPopUp.showInfoPopup({
                      topText : 'Az üzenet küldése sikeres!',
                      text : 'Sikeresen továbbítottad az üzenetet a felhasználónak',
                      type : 'info'
                   });
               }
            });
            
        });
    },
    
    openPage: function(page,getp, href){
       var messagesList = $('.messages .messagesList');
        var selectedPage= messagesList.find('.p.'+page);
        messagesList.find('.p.active').fadeOut().removeClass('active');
        selectedPage.fadeIn().addClass('active');
        
        if(page != 'newMessage'){
            selectedPage.html(MessagingShared.loadingDivHtml);
            $.post(href, {
                page : getp
            }).done(function(h){
               selectedPage.html(h); 
            });
        }
    }
    
}
