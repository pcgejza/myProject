UploadWindow = {
    
    uploadWindowReveal: null,
    uploadFilesButton: null,
    sendButton: null,
    files: null,
    allSubject: [],
    
    uploadFILE_URL: null,
    getUploadWindowURL: null,
    fileRenameURL: null,
    
    updateFilesSubjectsURL: null,
    
    msInput: null,
    
    init: function(){
        this.initVariables();
        this.bindUIActions();
    },
    
    initVariables: function(){
        this.uploadFilesButton = $('.uploadFiles');
        this.uploadWindowReveal = $('.upload-window-reveal');
    },
    
    bindUIActions: function(){
        if(this.uploadFilesButton.length == 0) return;
        
        this.uploadFilesButton.click(function(){
            UploadWindow.showWindow();
        });
    },
    
    showWindow: function(){
        UploadWindow.uploadWindowReveal.reveal({
            closeOnBackgroundClick: false
        });     
        if(UploadWindow.uploadWindowReveal.hasClass('not-loaded')){
            UploadWindow.uploadWindowReveal.addClass('loading-reveal');
            $.post(UploadWindow.getUploadWindowURL).done(function(h){
                UploadWindow.uploadWindowReveal.removeClass('loading-reveal');
                UploadWindow.uploadWindowReveal.removeClass('not-loaded');
                UploadWindow.uploadWindowReveal.html(h);   
                UploadWindow.sendButton = UploadWindow.uploadWindowReveal.find('.uploadAllFiles');
                UploadWindow.bindWindowActions();
                UploadCore.init(UploadWindow.uploadWindowReveal.find('form #upload_file'));
             });
        }
    },
    
    bindWindowActions: function(){
        
        var formFile = this.uploadWindowReveal.find('form #upload_file');
        var form = this.uploadWindowReveal.find('form');
        var formSubjects = this.uploadWindowReveal.find('#subjects-textarea');
        
        var sIn = $('.subjectsinput').magicSuggest({
            //resultAsString: true,
            width: 590,
            sortOrder: 'name',
            displayField: 'name',
            expandOnFocus: true,
            data: UploadWindow.allSubject
        });
        
        this.msInput = sIn;
        /*
        
        $('#ms-input-0').blur(function(){
          var toSend = sIn.getSelectedItems();
          UploadCore.uploadSubjects(toSend);
        });
*/
        this.uploadWindowReveal.find('.exit').unbind('click');
        this.uploadWindowReveal.find('.exit').bind('click',function(){
            UploadWindow.hide();
        });
        
        this.uploadWindowReveal.find('.upload').unbind('click');
        this.uploadWindowReveal.find('.upload').bind('click', function(e){
            e.preventDefault();
            if(UploadCore.getProcessesLength() == 0){
                formFile.click();
            }else{
                InfoPopUp.showInfoPopup({
                    type: 'error',
                    topText : "Hiba",
                    text : "Éppen tart a file feltöltés...",
                    closeFunction: function() {
                        UploadWindow.showWindow();
                    }
                })
            }
        });
        
        this.sendButton.unbind('click');
        this.sendButton.bind('click', function(){
            if(UploadCore.getProcessesLength() == 0){
                UploadCore.uploadToServer();
            }else{
                InfoPopUp.showInfoPopup({
                    type: 'error',
                    topText : "Hiba",
                    text : "Éppen tart a file feltöltés...",
                    closeFunction: function(){
                        UploadWindow.showWindow();
                    }
                });
            }
        });
        
        
        $('.uploadWindowContent A.restartB')
                .unbind('click')
                .bind('click', function(){
                    UploadWindow.restartWindow();
        });
    },
    
    // az ablak alaphelyzetbe állítása
    restartWindow: function(){
        // dobozok elrejtése valamint a táblázat ürítése
        $('.uploadWindowContent')
                .find('.postInputChangeElements,.uploadElements')
                    .addClass('hide')
                    .end()
                .find('.uploadElements table tbody tr').remove();
        
        // a kiválasztott fájlok törlése a tömbből
        UploadCore.resetFiles().removeALLElementsFromToSendFilesArr();
        
        // összes tantárgy törlése:
        this.msInput.clear();
    },
    
    handleFileSelect: function(){
        this.fileInput = fileInput;
        var formData = new FormData();
        var acceptedImagesNo = 0;
        var countLoadedImages = 0;
        var filesLength = document.getElementById('upload_file').files.length;  
    },
    
    addUploadFilesButton: function(){
        if(this.uploadFilesButton.length == 0){
            $('.page .contentHolder').first().prepend('<span class="upload-icon uploadFiles"></span>');
            this.init();
        }
    },
    
    hide: function(){
        this.uploadWindowReveal.trigger('reveal:close');
    },
    
    addQtipToUploads: function(){
        this.uploadWindowReveal.find('table *[title]').each(function(){
             $(this).qtip({
                show: 'mouseenter',
                hide: 'mouseleave',    
                position: {
                       my: 'bottom center',  // Position my top left...
                       at: 'top center', // at the bottom right of...
                   }, 
            });
        });
    },
    
    showMiniLoading: function(){
        this.uploadWindowReveal.find('table').css('opacity', '0.4');
        this.uploadWindowReveal.find('.mini-loading').removeClass('hide');
    },
    
    hideMiniLoading: function(){
        this.uploadWindowReveal.find('table').css('opacity', '1');
        this.uploadWindowReveal.find('.mini-loading').addClass('hide');
    },
    
    bindTableElementActions: function(){
        this.uploadWindowReveal.find('table .removeRow').unbind('click');
        this.uploadWindowReveal.find('table .removeRow').bind('click', function(){
            $(this).qtip('destroy');
            var thisRow = $(this).parents('tr').first();
            var id = thisRow.data('id');
            thisRow.hide('slow', function(){ 
                thisRow.remove(); 
            });
            UploadCore.removeElementFromToSendFilesArr(id);
            if(UploadCore.toSendFilesArr.length==0){
                $('.postInputChangeElements').addClass('hide');
            }
        });
        
        this.uploadWindowReveal.find('table .renameFile')
                .unbind('click')
                .bind('click', function(){
                    if($(this).find('input').length == 0){
                        if(UploadCore.isProgress()){
                            InfoPopUp.showInfoPopup({
                                type: 'error',
                                topText : "Hiba",
                                text : 'Fájl feltöltés közben nem végezheted el ezt a műveletet, kérlek várd meg míg feltöltődik a fájl!',
                                closeFunction:  function(){
                                    UploadWindow.showWindow();
                                }
                            });
                            return;
                        }
                        var thisO = $(this).html();
                        $(this).html('');
                        var $this = $(this);
                        var tr = $(this).parents('tr').first();
                        var index = tr.data('id');
                        console.log('index : '+index);
                        
                        $('<input type="text">')
                                .val(thisO)
                                .blur(function(){
                                    $this.html($(this).val());
                                    if($.type(tr.attr('fileid')) != 'undefined'){
                                        UploadCore.renameFileAjax(tr.attr('fileid'), $(this).val());
                                    }else{
                                        UploadCore.toSendFilesArr[index].name = $(this).val();
                                    }
                                }).appendTo($this).focus();
                        
                    }
                });
    },
    
    
}