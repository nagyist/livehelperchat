var lhcError = {
    log : function(message, filename, lineNumber, stack, column) {
            var e;
            e = {};
            e.message = message || "";
            e.location = location && location.href ? location.href : "";
            e.message += "\n" + window.navigator.userAgent;
            e.file = filename || "";
            e.line = lineNumber || "";
            e.column = column || "";
            e.stack = stack ? JSON.stringify(stack) : "";
            e.stack = e.stack.replace(/(\r\n|\n|\r)/gm, "");
            var xhr = new XMLHttpRequest();
            xhr.open( "POST", WWW_DIR_JAVASCRIPT + '/audit/logjserror', true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.send( "data=" + encodeURIComponent( JSON.stringify(e) ) );
    }
}

window.addEventListener('error', function(e) {
    if (lhcError && (e.filename.indexOf('js_static') !== -1 || e.filename.indexOf('compiledtemplates') !== -1 || e.filename.indexOf('defaulttheme') !== -1)) {
       lhcError.log(e.message, e.filename, e.lineNumber || e.lineno, e.error.stack, e.colno);
    }
})

try {

function csrfSafeMethod(method) {
    // these HTTP methods do not require CSRF protection
    return (/^(GET|HEAD|OPTIONS|TRACE)$/.test(method));
};

$.ajaxSetup({
    crossDomain: false, // obviates need for sameOrigin test
    cache: false,
    beforeSend: function(xhr, settings) {
        if (!csrfSafeMethod(settings.type)) {
            xhr.setRequestHeader("X-CSRFToken", confLH.csrf_token);
        }
    }
});

$.postJSON = function(url, data, callback) {
	return $.post(url, data, callback, "json");
};

var LHCCallbacks = {};

function lh(){

    this.wwwDir = WWW_DIR_JAVASCRIPT;
    this.addmsgurl = "chat/addmsgadmin/";

    this.syncadmin = "chat/syncadmin/";
    this.closechatadmin = "chat/closechatadmin/";
    this.deletechatadmin = "chat/deletechatadmin/";

    this.syncadmininterfaceurl = "chat/syncadmininterface/";
    this.accepttransfer = "chat/accepttransfer/";
    this.trasnsferuser = "chat/transferuser/";

    this.channel = null;

    this.ignoreAdminSync = false;
    this.disableremember = false;
    this.operatorTyping = false;
    this.forceBottomScroll = false;
    this.appendSyncArgument = '';
    this.nodeJsMode = false;
    this.previous_chat_id = 0;

    this.gmaps_loaded = false;

    // Disable sync, is used in angular controllers before migration to new JS structure
    this.disableSync = false;
    
    // On chat hash and chat_id is based web user chating. Hash make sure chat security.
    this.chat_id = null;
    this.hash = null;

    this.soundIsPlaying = false;
    this.soundPlayedTimes = 0;
    
    // Used for synchronization for user chat
    this.last_message_id = 0;

    // Is synchronization under progress
    this.isSinchronizing = false;
    
    // is Widget mode
    this.isWidgetMode = false;

    // is Embed mode
    this.isEmbedMode = false;

    this.syncroRequestSend = false;

    this.currentMessageText = '';

    this.setSynchronizationRequestSend = function(status)
    {
        this.syncroRequestSend = status;
    };

    // Chats currently under synchronization
    this.chatsSynchronising = [];
    this.chatsSynchronisingMsg = [];
    
    // Notifications array
    this.notificationsArray = [];

    this.notificationsArrayMail = [];

    this.speechHandler = false;
    
    // Block synchronization till message add finished
    this.underMessageAdd = false;


    this.closeWindowOnChatCloseDelete = false;

    this.userTimeout = false;

    this.lastOnlineSyncTimeout = false;

    this.setwwwDir = function (wwwdir){
        this.wwwDir = wwwdir;
    };

    this.setDisableRemember = function (value)
    {
        this.disableremember = value;
    };

    this.setSynchronizationStatus = function(status)
    {
        this.underMessageAdd = status;
    };

    this.startCoBrowse = function(chat_id)
    {
        popupWindow = window.open(this.wwwDir + 'cobrowse/browse/'+chat_id,'chatwindow-cobrowse-chat-id-'+chat_id,"menubar=1,resizable=1,width=800,height=650");

        if (popupWindow !== null) {
            popupWindow.focus();
        }

        return false;
    };

    this.tabIconContent = 'face';
    this.tabIconClass = 'icon-user-status material-icons icon-user-online';
    
    this.audio = typeof window.Audio !== "undefined" ? new Audio() : null;
    if (this.audio !== null) {
        this.audio.autoplay = 'autoplay';
    }

    this.reloadTab = function(chat_id, tabs, nick, internal)
    {
        $('#ntab-chat-'+chat_id).text(nick);

        if ($('#CSChatMessage-'+chat_id).length != 0){
            $('#CSChatMessage-'+chat_id).unbind('keydown', function(){});
            $('#CSChatMessage-'+chat_id).unbind('keyup', function(){});
        }

        this.removeSynchroChat(chat_id, true);
        this.removeBackgroundChat(chat_id);
        this.hideNotification(chat_id);
        var inst = this;
        $.get(this.wwwDir +'chat/adminchat/'+chat_id+'/(remember)/true', function(data) {
            $('#chat-id-'+chat_id).html(data);
            inst.setFocus(chat_id);
            inst.rememberTab(chat_id);
            inst.addQuateHandler(chat_id);
            inst.loadMainData(chat_id);
            ee.emitEvent('chatTabLoaded', [chat_id]);
            ee.emitEvent('chatStartTab', [chat_id, {name: nick, focus: true}]);
            !internal && inst.channel && inst.channel.postMessage({'action':'reload_chat','args':{'nick': nick, 'chat_id' : parseInt(chat_id)}});
        });
    }

    this.loadMainData = function(chat_id) {

        var _that = this;

        $.getJSON(this.wwwDir + 'chat/loadmaindata/' + chat_id, { }, function(data) {
            $.each(data.items, function( index, dataElement ) {
                var el = $(dataElement.selector);

                if (typeof dataElement.attr !== 'undefined') {
                    $.each(dataElement.attr, function( attr, data ) {
                        if (attr == 'text') {
                            el.text(data);
                        } else {
                            el.attr(attr,data);
                        }
                    });
                }

                if (typeof dataElement.action !== 'undefined') {
                    if (dataElement.action == 'hide') {
                        el.hide();
                    } else if (dataElement.action == 'remove_class') {
                        el.removeClass(dataElement.class);
                    } else if (dataElement.action == 'add_class') {
                        el.addClass(dataElement.class);
                    } else if (dataElement.action == 'keyupmodal') {
                        el.bind('keyup', dataElement.event_data.a + '+' + dataElement.event_data.b, function() {
                            lhc.revealModal({'url':WWW_DIR_JAVASCRIPT+dataElement.event_data.url});
                        });
                    } else if (dataElement.action == 'keyup') {

                        el.bind('keyup', dataElement.event_data.a + '+' + dataElement.event_data.b, function() {
                            var pdata = {
                                msg	: '!'+dataElement.event_data.cmd
                            };

                            $.postJSON(_that.wwwDir + _that.addmsgurl + chat_id, pdata , function(data) {

                                if (LHCCallbacks.addmsgadmin) {
                                    LHCCallbacks.addmsgadmin(chat_id);
                                };

                                ee.emitEvent('chatAddMsgAdmin', [chat_id]);

                                if (data.r != '') {
                                    $('#messagesBlock-'+chat_id).append(data.r).scrollTop($("#messagesBlock-"+chat_id).prop("scrollHeight")).find('.pending-storage').remove();
                                };

                                if (data.hold_removed === true) {
                                    $('#hold-action-'+chat_id).removeClass('btn-outline-info');
                                } else if (data.hold_added === true) {
                                    $('#hold-action-'+chat_id).addClass('btn-outline-info');
                                }

                                if (data.update_status === true) {
                                    _that.updateVoteStatus(chat_id);
                                }

                                _that.syncadmincall();

                                return true;
                            });
                        });

                    } else if(dataElement.action == 'show') {
                        el.show();
                    } else if(dataElement.action == 'remove') {
                        el.remove();
                    } else if(dataElement.action == 'event') {
                        ee.emitEvent(dataElement.event_name, dataElement.event_value);
                    } else if(dataElement.action == 'click') {
                        if (confLH.no_scroll_bottom !== 1){
                            el.attr('auto-scroll',1);
                        }
                        el.click();
                    }
                }
            });

            ee.emitEvent('mainChatDataLoaded', [chat_id, data]);

        }).fail(function() {

        });
    }

    this.getSelectedText = function () {
        var text = '';
        var selection;

        if (window.getSelection) {
            selection = window.getSelection();
            text = selection.toString();
        } else if (document.selection && document.selection.type !== 'Control') {
            selection = document.selection.createRange();
            text = selection.text;
        }

        return {
            selection: selection,
            text: text
        };
    }

    this.popoverShown = false;
    this.popoverShownNow = false
    this.selection = null;

    this.mouseContextMenu = function(e) {

        if (e.which == 3 && typeof $(this).attr('id') !== 'undefined') {

            $('.popover-copy').popover('dispose');

            var selected = e.data.that.getSelectedText();
            var hasSelection = false;
            if (selected.text.length && (e.data.that.selection === null || e.data.that.selection.text !== selected.text)) {
                hasSelection = true;
                e.data.that.selection = selected;
            }

            var msgId = $(this).attr('id').replace('msg-','');
            var canEdit = !$('#CSChatMessage-'+e.data.chat_id).attr('readonly');
            var isOwner = ($('#CSChatMessage-'+e.data.chat_id).attr('disable-edit') !== "true" && (
                        ($(this).attr('data-op-id') == confLH.user_id && ($('#CSChatMessage-'+e.data.chat_id).attr('edit-all') === "true") || ($('#messagesBlock-' + e.data.chat_id + ' > div.message-admin').length > 0 && msgId == $('#messagesBlock-' + e.data.chat_id + ' > div.message-admin').last().attr('id').replace('msg-',''))) ||
                        ($('#CSChatMessage-'+e.data.chat_id).attr('edit-op') === "true" && (parseInt($(this).attr('data-op-id')) > 0 || parseInt($(this).attr('data-op-id')) === -2)) ||
                        ($('#CSChatMessage-'+e.data.chat_id).attr('edit-vis') === "true" && parseInt($(this).attr('data-op-id')) === 0)
                    )
             ) && canEdit;

            var canRemove = (parseInt($(this).attr('data-op-id')) === 0 && $('#CSChatMessage-'+e.data.chat_id).attr('remove-msg-vi') === "true") || ((parseInt($(this).attr('data-op-id')) === -2 || parseInt($(this).attr('data-op-id')) > 0) && $('#CSChatMessage-'+e.data.chat_id).attr('remove-msg-op') === "true");

            var quoteParams = {
                placement:'right',
                trigger:'manual',
                animation:false,
                html:true,
                container:'#chat-id-'+e.data.chat_id,
                template : '<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"></div></div>',
                content:function(){
                    return (canEdit ? ('<a href="#" id="copy-popover-'+e.data.chat_id+'" ><i class="material-icons">&#xE244;</i>'+confLH.transLation.quote+'</a><br/>') : '') + (canRemove ? '<a href="#" id="remove-popover-'+e.data.chat_id+'" ><i class="material-icons">delete</i>'+confLH.transLation.remove+'</a><br/>' : '') + (isOwner ? '<a href="#" id="edit-popover-'+e.data.chat_id+'" ><i class="material-icons">edit</i>'+confLH.transLation.edit+'</a><br/>' : '') + '<a href="#" id="ask-help-popover-'+e.data.chat_id+'" ><i class="material-icons">supervisor_account</i>'+confLH.transLation.ask_help+'</a>' + (hasSelection ? '<br/><a href="#" id="copy-text-popover-'+e.data.chat_id+'" ><i class="material-icons">content_copy</i>'+confLH.transLation.copy+' (Ctrl+C)</a>' : '') + (!hasSelection ? '<br/><a href="#" id="copy-all-text-popover-'+e.data.chat_id+'" ><i class="material-icons">content_copy</i>'+confLH.transLation.copy+' (Ctrl+C)</a><br/><a href="#" id="copy-group-text-popover-'+e.data.chat_id+'" ><i class="material-icons">content_copy</i>'+confLH.transLation.copy_group+'</a>' : '')+(!hasSelection ? '<br/><a href="#" id="translate-msg-'+e.data.chat_id+'" ><i class="material-icons">language</i>'+confLH.transLation.translate+'</a>' : '');
                }
            }
            
            var containerPopover = $('#messagesBlock-'+e.data.chat_id+' > #msg-'+msgId+' > .msg-body');

            if (containerPopover.length == 0) return ;

            ee.emitEvent('quoteActionRight', [quoteParams, e.data.chat_id, msgId]);

            containerPopover.popover(quoteParams).popover('show').addClass('popover-copy');

            $('#copy-popover-'+e.data.chat_id).click(function(event){
                event.stopPropagation();
                event.preventDefault();
                $.getJSON(e.data.that.wwwDir + 'chat/quotemessage/' + msgId, function(data){
                    data.msg && e.data.that.insertTextToMessageArea(e.data.chat_id, data.msg, msgId);
                    e.data.that.hidePopover();
                });
            });

            $('#remove-popover-'+e.data.chat_id).click(function(event){
                event.stopPropagation();
                event.preventDefault();
                $.postJSON(e.data.that.wwwDir + 'chat/deletemsg/' + e.data.chat_id + '/' + msgId, function(data){
                    if (data.error == 'f') {
                        e.data.that.hidePopover();
                        $('#msg-'+msgId).remove();
                    } else {
                        alert(data.result);
                    }
                });
            });

            $('#ask-help-popover-'+e.data.chat_id).click(function(event){
                event.stopPropagation();
                event.preventDefault();
                $.getJSON(e.data.that.wwwDir + 'chat/quotemessage/' + msgId, function(data){
                    if (!$('#private-chat-tab-link-'+e.data.chat_id).attr('private-loaded')) {
                        $('#private-chat-tab-link-'+e.data.chat_id).attr('private-loaded',true).click();
                        (new bootstrap.Tab(document.querySelector('#private-chat-tab-link-'+e.data.chat_id))).show();
                        ee.emitEvent('privateChatStart', [e.data.chat_id,{'default_message':data.msg}]);
                    } else {
                        $('#private-chat-tab-link-'+e.data.chat_id).attr('private-loaded',true).click();
                        (new bootstrap.Tab(document.querySelector('#private-chat-tab-link-'+e.data.chat_id))).show();
                        ee.emitEvent('groupChatPrefillMessage', [e.data.chat_id,data.msg]);
                    }
                    e.data.that.hidePopover();
                });
            });

            !hasSelection && $('#translate-msg-'+e.data.chat_id).click(function(event){
                event.stopPropagation();
                event.preventDefault();
                lhc.methodCall('lhc.translation','translateMessageVisitor',{'msg_id':msgId,'chat_id':e.data.chat_id});
                e.data.that.hidePopover();
            });

            !hasSelection && $('#copy-all-text-popover-'+e.data.chat_id).click(function(event){
                event.stopPropagation();
                event.preventDefault();
                $.getJSON(e.data.that.wwwDir + 'chat/quotemessage/' + msgId, function(data){
                    lhinst.copyContentRaw(data.msg);
                    e.data.that.hidePopover();
                });
            });

            !hasSelection && $('#copy-group-text-popover-'+e.data.chat_id).click(function(event){
                event.stopPropagation();
                event.preventDefault();
                $.getJSON(e.data.that.wwwDir + 'chat/quotemessage/' + msgId +'/(type)/group', function(data){
                    lhinst.copyContentRaw(data.msg);
                    e.data.that.hidePopover();
                });
            });

            isOwner && $('#edit-popover-'+e.data.chat_id).click(function(event){
                event.stopPropagation();
                event.preventDefault();
                $.getJSON(e.data.that.wwwDir + 'chat/editprevious/' + e.data.chat_id + '/' + msgId, function(data){
                    if (data.error == 'f') {

                        var textArea = $('#CSChatMessage-'+e.data.chat_id);

                        if (textArea.prop('nodeName') == 'LHC-EDITOR') {
                            textArea[0].setContent(data.msg,{"convert_bbcode" : true});
                            textArea.attr('data-msgid',data.id).addClass('edit-mode');
                            textArea[0].setFocus();
                        } else {
                            textArea.val(data.msg).attr('data-msgid',data.id).addClass('edit-mode');
                            textArea.focus();
                        }

                        $('#msg-'+data.id).addClass('edit-mode');

                    } else {
                        alert(data.result);
                    }
                });
                e.data.that.hidePopover();
            });

            hasSelection && $('#copy-text-popover-'+e.data.chat_id).click(function(event){
                event.stopPropagation();
                event.preventDefault();
                lhinst.copyContentRaw(e.data.that.getSelectedTextPlain());
                e.data.that.hidePopover();
            });

            e.data.that.popoverShown = true;
            e.data.that.popoverShownNow = false;

            return false;
        }
    }

    this.copyContentRaw = function(content){
        var textArea = document.createElement("textarea");
        textArea.value = content;

        // Avoid scrolling to bottom
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";

        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            var successful = document.execCommand('copy');
        } catch (err) {
            alert('Oops, unable to copy');
        }
        document.body.removeChild(textArea);
    }

    this.insertTextToMessageArea = function (chat_id, msg, msgId) {
        var textArea = $('#CSChatMessage-'+chat_id);

        if (textArea.prop('nodeName') == 'LHC-EDITOR') {
            textArea[0].insertContent('[quote'+ (msgId ? '=' + msgId : '') + ']'+msg+'[/quote]',{"new_line":true,"convert_bbcode" : true});
        } else {
            var textAreaVal = textArea.val().replace(/^\s*\n/g, "");
            textArea.val((textAreaVal != '' ? textAreaVal + '[quote'+ (msgId ? '=' + msgId : '') + ']' + msg + '[/quote]' : '[quote'+ (msgId ? '=' + msgId : '') + ']'+msg+'[/quote]')+"\n").focus();

            var ta = textArea[0];
            var maxrows = 30;
            var lh = ta.clientHeight / ta.rows;
            while (ta.scrollHeight > ta.clientHeight && !window.opera && ta.rows < maxrows) {
                ta.style.overflow = 'hidden';
                ta.rows += 1;
            }

            if (ta.scrollHeight > ta.clientHeight) ta.style.overflow = 'auto';
        }
    }

    this.mouseClicked = function (e) {
        selected = e.data.that.getSelectedText();

        $('.popover-copy').popover('dispose');

        if (selected.text.length && (e.data.that.selection === null || e.data.that.selection.text !== selected.text)) {

            e.data.that.selection = selected;

            var quoteParams = {
                placement:'right',
                trigger:'manual',
                animation:false,
                html:true,
                container:'#chat-id-'+e.data.chat_id,
                template : '<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"></div></div>',
                content:function(){return '<a href="#" id="copy-popover-'+e.data.chat_id+'" ><i class="material-icons">&#xE244;</i>'+confLH.transLation.quote+'</a>'; }
            }

            var placement = typeof $(this).attr('id') !== 'undefined' ? '#messagesBlock-'+e.data.chat_id+' > #msg-'+$(this).attr('id').replace('msg-','')+' > .msg-body' : this;

            var containerPopover = $(placement);

            if (containerPopover.length == 0) return ;

            ee.emitEvent('quoteAction', [quoteParams,e.data.chat_id]);

            containerPopover.popover(quoteParams).popover('show').addClass('popover-copy');

            $('#copy-popover-'+e.data.chat_id).click(function(){
                lhinst.quateSelection(e.data.chat_id);
            });


            e.data.that.popoverShown = true;
            e.data.that.popoverShownNow = true;
        } else {
            e.data.that.selection = null;
        }
    }

    this.addQuateHandler = function(chat_id)
    {
        this.popoverShown = false;
        $('#messagesBlock-'+chat_id+' > .message-row:not([qt])')
            .on('mouseup',{chat_id:chat_id, that : this}, lhinst.mouseClicked)
            .on('contextmenu', {chat_id:chat_id, that : this}, lhinst.mouseContextMenu).attr('qt',1);
    }

    this.getSelectedTextPlain = function() {
        var textToPaste = this.selection.text.replace(/[\uD7AF\uD7C7-\uD7CA\uD7FC-\uF8FF\uFA6E\uFA6F\uFADA]/g,'');

        textToPaste = textToPaste.replace(/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}(.*)/gm,'');
        textToPaste = textToPaste.replace(/^[0-9]{2}:[0-9]{2}:[0-9]{2}(.*)/gm,'');
        textToPaste = textToPaste.replace(/^\s*\n/gm, "");
        textToPaste = textToPaste.replace(/^ /gm, "");

        return textToPaste;
    }

    this.quateSelection = function (chat_id) {
        $('.popover-copy').popover('dispose');
        var textToPaste = this.getSelectedTextPlain();
        window.textreplace = textToPaste;
        this.insertTextToMessageArea(chat_id, textToPaste);
        this.popoverShown = false;
    };

    this.hidePopover = function () {

        if (this.popoverShownNow === true) {
            this.popoverShownNow = false;
        } else {
            if (this.popoverShown === true) {
                this.popoverShown = false;
                $('.popover-copy').popover('dispose');
            }
        }
    };

    this.logOpenTrace = [];

    this.addOpenTrace = function(log) {
        this.logOpenTrace.push(log);
    }

    this.animateClick = function(chat_id) {
        $('#chat-tab-link-'+chat_id).addClass('blink-tab-item');
        setTimeout(function() {
            $('#chat-tab-link-'+chat_id).removeClass('blink-tab-item');
        }, 1000);
    }

    this.addTab = function(tabs, url, name, chat_id, focusTab, position) {
    	// If tab already exits return
    	if (tabs.find('#chat-tab-link-'+chat_id).length > 0) {
            lhinst.logOpenTrace = [];
            this.animateClick(chat_id);
    		return ;
    	}

    	var hideTabs = confLH.new_dashboard && confLH.hide_tabs && document.getElementById('tabs-dashboard') !== null ? ' d-none' : '';

    	var contentLi = '<li role="presentation" id="chat-tab-li-'+chat_id+'" class="nav-item'+hideTabs+'"><span onclick="return lhinst.removeDialogTab('+chat_id+',$(\'#tabs\'),true)" class="material-icons icon-close-chat">close</span><a class="nav-link chat-nav-item" href="#chat-id-'+chat_id+'" id="chat-tab-link-'+chat_id+'" aria-controls="chat-id-'+chat_id+'" role="tab" data-bs-toggle="tab"><i id="msg-send-status-'+chat_id+'" class="material-icons send-status-icon icon-user-online">send</i><i id="user-chat-status-'+chat_id+'" class="chat-tab-content '+this.tabIconClass+'">'+this.tabIconContent+'</i><span class="ntab" id="ntab-chat-'+chat_id+'">' + name.replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</span></a></li>';

    	if (typeof position === 'undefined' || parseInt(position) == 0) {
    		tabs.find('> ul').append(contentLi);
    	} else {
    		tabs.find('> ul > li:eq('+ (position - 1)+')').after(contentLi);
    	};

    	$('#chat-tab-link-'+chat_id).click(function() {

    	    lhinst.previous_chat_id > 0 && $('#unread-separator-'+lhinst.previous_chat_id).remove();
            lhinst.previous_chat_id = chat_id;

            setTimeout(function() {
                lhinst.setFocus(chat_id);
            },2);

    		var inst = $(this);
    		setTimeout(function(){
    			inst.find('.msg-nm').remove();

    			var scrollNeeded = false;

                if (inst.hasClass('has-pm')) {
                    scrollNeeded = true;
                    inst.removeClass('has-pm');
                }

                if (scrollNeeded == true) {
                    $('#messagesBlock-'+chat_id).prop('scrollTop',$('#messagesBlock-'+chat_id).prop('scrollHeight'));
                }
    		},500);

            ee.emitEvent('chatTabClicked', [chat_id, inst]);
    	});
    	
    	var hash = window.location.hash.replace('#/','#');	

    	var inst = this;

        var logOpen = '';

        if (lhinst.logOpenTrace.length > 0) {
            logOpen = '/(ol)/' + lhinst.logOpenTrace.join('/');
            lhinst.logOpenTrace = [];
        }

    	$.get(url + logOpen, function(data) {

    	    if (data == '') {
                inst.removeDialogTab(chat_id,tabs,true);
    	        return;
            }
    	    
    		if ((typeof focusTab === 'undefined' || focusTab === true || hash == '#chat-id-'+chat_id) && url.indexOf('(arg)/backgroundid') === -1) {
	    		tabs.find('> ul > li > a.active').removeClass("active");
	    		tabs.find('> ul > #chat-tab-li-'+chat_id+' > a').addClass("active");
	    		tabs.find('> div.tab-content > div.active').removeClass('active');
	    		tabs.find('> div.tab-content').append('<div role="tabpanel" class="tab-pane active chat-tab-pane" id="chat-id-'+chat_id+'"></div>');
	    		window.location.hash = '#/chat-id-'+chat_id;
                tabs.addClass('chat-tab-selected');
	    	} else {
	    		tabs.find('> div.tab-content').append('<div role="tabpanel" class="tab-pane chat-tab-pane" id="chat-id-'+chat_id+'"></div>');
	    	}
    		 		
    		$('#chat-id-'+chat_id).html(data);

            if (typeof focusTab === 'undefined' || focusTab === true || hash == '#chat-id-'+chat_id) {
                inst.setFocus(chat_id);
            }

            if (inst.disableremember == false) {
                inst.rememberTab(chat_id);
            }
            inst.addQuateHandler(chat_id);
            inst.loadMainData(chat_id);
            ee.emitEvent('chatTabLoaded', [chat_id]);
    	});
    };

    this.rememberTab = function(chat_id) {
        if (localStorage) {
            try{
                chat_id = parseInt(chat_id);

                var achat_id = localStorage.getItem('achat_id');
                var achat_id_array = new Array();

                if (achat_id !== null) {
                    var achat_id_array = achat_id.split(',').map(Number);
                }

                if (achat_id_array.indexOf(chat_id) === -1) {
                    achat_id_array.push(chat_id);
                }

                localStorage.setItem('achat_id',achat_id_array.join(','));
            } catch (e) {
                console.log(e);
            }
        }
    };
    
    this.forgetChat = function (chat_id,listId) {
        if (localStorage) {
            try {
                chat_id = parseInt(chat_id);

                var achat_id = localStorage.getItem(listId);
                var achat_id_array = new Array();

                if (achat_id !== null) {
                    achat_id_array = achat_id.split(',').map(Number);
                }

                if (achat_id_array.indexOf(chat_id) !== -1){
                    achat_id_array.splice(achat_id_array.indexOf(chat_id), 1);
                }

                localStorage.setItem(listId,achat_id_array.join(','));
            } catch (e) {
                console.log(e);
            }

        }
    };
    
    this.attachTabNavigator = function() {
    	$('#tabs > ul.nav > li > a').click(function(){
    		$(this).find('.msg-nm').remove();
    		$(this).removeClass('has-pm');
    	});
    };

    this.holdAction = function(chat_id, inst) {

        var textArea = $("#CSChatMessage-"+chat_id);

        if (textArea.is("[readonly]")) {
            return;
        }

    	var _this  = this;
        $.postJSON(this.wwwDir + 'chat/holdaction/' + chat_id ,{'sel' : inst.hasClass('btn-outline-info'), 'op' : inst.attr('data-type') }, function(data) {
            if (data.error == false) {

                if (data.hold == true) {
                    inst.addClass('btn-outline-info');
                    if (inst.attr('data-type') == 'usr') {
                        $('#hold-action-'+chat_id).removeClass('btn-outline-info');
                    } else {
                        $('#hold-action-usr-'+chat_id).removeClass('btn-outline-info');
                    }
                } else {
                    inst.removeClass('btn-outline-info');
                }

				if (data.msg != '') {
					$('#messagesBlock-'+chat_id).append(data.msg).scrollTop($("#messagesBlock-"+chat_id).prop("scrollHeight"));
				}

                _this.syncadmincall();
            } else {
                alert(data.msg);
            }
        });
	},

    this.copyContent = function(inst){

        var textArea = document.createElement("textarea");

        var copyContent = '';

        if (inst.attr('data-copy-id')) {
            // If data-copy-id is provided, get content from the referenced element
            var sourceElement = document.getElementById(inst.attr('data-copy-id'));
            if (sourceElement) {
                copyContent = sourceElement.value || sourceElement.innerHTML;
            }
        } else {
            // Otherwise use the data-copy attribute directly
            copyContent = inst.attr('data-copy');
        }

        textArea.value = copyContent;

        // Avoid scrolling to bottom
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";

        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            var successful = document.execCommand('copy');
        } catch (err) {
            alert('Oops, unable to copy');
        }

        document.body.removeChild(textArea);

        var toolTip = new bootstrap.Tooltip(inst,{
            trigger: 'click',
            placement: 'top'
        });

        function setTooltip(message) {
            toolTip.show();
        }

        function hideTooltip() {
            setTimeout(function() {
                toolTip.dispose();
            }, 1000);
        }

        setTooltip();
        hideTooltip();

    },

	this.copyMessages = function(inst) {

        $('#chat-copy-messages').select();
        document.execCommand("copy");

        inst.tooltip({
            trigger: 'click',
            placement: 'top'
        });

        function setTooltip(message) {
            inst.tooltip('hide')
                .attr('data-original-title', message)
                .tooltip('show');
        }

        function hideTooltip() {
            setTimeout(function() {
                inst.tooltip('hide');
            }, 3000);
        }

        setTooltip(inst.attr('data-success'));
        hideTooltip();


        return false;
	},

    this.removeDialogTabGroup = function(chat_id, tabs)
    {
        ee.emitEvent('unloadGroupChat', [chat_id]);
        var location = this.smartTabFocus(tabs, chat_id);
    };

    this.removeDialogTabMail = function(chat_id, tabs, hidetabs, internal)
    {
        !internal && this.channel && this.channel.postMessage({'action':'close_mail','args':{'mail_id' : chat_id}});

        ee.emitEvent('unloadMailChat', [chat_id]);
        
        var location = this.smartTabFocus(tabs, chat_id);

        setTimeout(function() {
            window.location.hash = location;
        },500);
    };

    this.addGroupTab = function(tabs, name, chat_id, background) {
        // If tab already exits return
        if (tabs.find('#chat-tab-link-'+chat_id).length > 0) {
            tabs.find('> ul > li > a.active').removeClass("active");
            tabs.find('> ul > li#chat-tab-li-'+chat_id+' > a').addClass("active");
            tabs.find('> div.tab-content > div.active').removeClass('active');
            tabs.find('> div.tab-content > #chat-id-'+chat_id).addClass('active');
            ee.emitEvent('groupChatTabClicked', [chat_id]);
            return ;
        }

        var contentLi = '<li role="presentation" id="chat-tab-li-'+chat_id+'" class="nav-item"><a class="nav-link" href="#chat-id-'+chat_id+'" id="chat-tab-link-'+chat_id+'" aria-controls="chat-id-'+chat_id+'" role="tab" data-bs-toggle="tab"><i id="msg-send-status-'+chat_id+'" class="material-icons send-status-icon icon-user-online">send</i><i class="whatshot blink-ani d-none text-warning material-icons">whatshot</i><i id="user-chat-status-'+chat_id+'" class="'+this.tabIconClass+'">group</i><span class="ntab" id="ntab-chat-'+chat_id+'">' + name.replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</span><span onclick="return lhinst.removeDialogTabGroup(\''+chat_id+'\',$(\'#tabs\'),true)" class="material-icons icon-close-chat">close</span></a></li>';

        tabs.find('> ul').append(contentLi);
        var hash = window.location.hash.replace('#/','#');

        var inst = this;

        if (background !== true) {
            tabs.find('> ul > li > a.active').removeClass("active");
            tabs.find('> ul > #chat-tab-li-'+chat_id+' > a').addClass("active");
            tabs.find('> div.tab-content > div.active').removeClass('active');
            tabs.find('> div.tab-content').append('<div role="tabpanel" class="tab-pane chat-tab-pane active" id="chat-id-'+chat_id+'"></div>');
        } else {
            tabs.find('> div.tab-content').append('<div role="tabpanel" class="tab-pane chat-tab-pane" id="chat-id-'+chat_id+'"></div>');
        }

        ee.emitEvent('groupChatTabLoaded', [chat_id]);
        
        $('#chat-tab-link-'+chat_id).click(function() {
            ee.emitEvent('groupChatTabClicked', [chat_id.replace('gc','')]);
        });
    };

    this.addMailTab = function(tabs, name, chat_id, background) {
        // If tab already exits return
        if (tabs.find('#chat-tab-link-'+chat_id).length > 0) {
            if (background !== true) {
                tabs.find('> ul > li > a.active').removeClass("active");
                tabs.find('> ul > li#chat-tab-li-'+chat_id+' > a').addClass("active");
                tabs.find('> div.tab-content > div.active').removeClass('active');
                tabs.find('> div.tab-content > #chat-id-'+chat_id).addClass('active');
            } else {
                this.animateClick(chat_id);
            }
            ee.emitEvent('mailChatTabClicked', [chat_id.replace('mc','')]);
            return ;
        }

        var contentLi = '<li role="presentation" id="chat-tab-li-'+chat_id+'" class="nav-item"><span onclick="return lhinst.removeDialogTabMail(\''+chat_id+'\',$(\'#tabs\'),true)" class="material-icons icon-close-chat">close</span><a class="nav-link" href="#chat-id-'+chat_id+'" id="chat-tab-link-'+chat_id+'" aria-controls="chat-id-'+chat_id+'" role="tab" data-bs-toggle="tab"><i id="msg-send-status-'+chat_id+'" class="material-icons send-status-icon icon-user-online">send</i><i class="whatshot blink-ani d-none text-warning material-icons">whatshot</i><i id="user-chat-status-'+chat_id+'" class="'+this.tabIconClass+'">mail_outline</i><span class="ntab" id="ntab-chat-'+chat_id+'">' + name.replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</span></a></li>';

        tabs.find('> ul').append(contentLi);
        var hash = window.location.hash.replace('#/','#');

        var inst = this;

        if (background !== true) {
            tabs.find('> ul > li > a.active').removeClass("active");
            tabs.find('> ul > #chat-tab-li-'+chat_id+' > a').addClass("active");
            tabs.find('> div.tab-content > div.active').removeClass('active');
            tabs.find('> div.tab-content').append('<div role="tabpanel" class="tab-pane active" id="chat-id-'+chat_id+'"></div>');
        } else {
            tabs.find('> div.tab-content').append('<div role="tabpanel" class="tab-pane" id="chat-id-'+chat_id+'"></div>');
        }

        ee.emitEvent('mailChatTabLoaded', [chat_id, {'background' : background}]); 

        $('#chat-tab-link-'+chat_id).click(function() {
            ee.emitEvent('mailChatTabClicked', [chat_id.replace('mc','')]);
        });
    };

    this.startGroupChat = function (chat_id, tabs, name, background) {
        this.addGroupTab(tabs, name, 'gc'+chat_id, background);
    }

    this.startMailChat = function (chat_id, tabs, name, background) {
        this.hideNotification(chat_id,'mail');
        this.addMailTab(tabs, name, 'mc'+chat_id, background);
    }

    this.hideShowAction = function(options) {

        var messagesBlock = $('#messagesBlock-' + options['chat_id']);

        var needScroll = (messagesBlock.prop('scrollTop') + messagesBlock.height() + 30) > messagesBlock.prop('scrollHeight')

        var msg = $('#message-more-'+options['id']);
        if (msg.hasClass('hide')) {
            msg.removeClass('hide');
            options['hide_show'] == false ? $('#hide-show-action-'+options['id']).remove() : $('#hide-show-action-'+options['id']).text(options['hide_text']);
        } else {
            msg.addClass('hide');
            if (options['hide_show'] == true) {
                $('#hide-show-action-'+options['id']).text(options['show_text']);
            }
        }
        
        needScroll && messagesBlock.scrollTop(messagesBlock.prop('scrollHeight'));
    }

    this.buttonAction = function(inst,payload) {
        var row = inst.closest('.message-row');
        $.getJSON(this.wwwDir + 'chat/abstractclick/' + row.attr('id').replace('msg-','') + '/' + payload, function(data) {
            if (data.error) {
                alert(data.error);
            } else if (data.replace_id && data.html) {
                var messagesBlock = $('#messagesBlock-' + data.chat_id);
                var needScroll = (messagesBlock.prop('scrollTop') + messagesBlock.height() + 30) > messagesBlock.prop('scrollHeight');
                $(data.replace_id).replaceWith(data.html);
                lhinst.addQuateHandler(data.chat_id);
                needScroll && messagesBlock.scrollTop(messagesBlock.prop('scrollHeight'));
            } else if (data.modal) {
                lhc.revealModal({'url':WWW_DIR_JAVASCRIPT+data.modal});
            }
        });
    }
    

    this.startChat = function (chat_id,tabs,name,focusTab,position) {
    	    	
    	this.removeBackgroundChat(chat_id);
    	this.hideNotification(chat_id);

    	$('#sub-tabs').length > 0 && $('#sub-tabs a[href=\'#sub-tabs-open\']').tab('show');
        var focusTabAction = typeof focusTab !== 'undefined' ? focusTab : true;

        if ( this.chatUnderSynchronization(chat_id) == false ) {        	
        	var rememberAppend = this.disableremember == false ? '/(remember)/true' : '';
        	this.addTab(tabs, this.wwwDir +'chat/adminchat/'+chat_id+rememberAppend, name, chat_id, focusTabAction, position);
        	var inst = this;
            if (this.ignoreAdminSync === false) {
                setTimeout(function() {
                    inst.syncadmininterfacestatic();
                },1000);
            }
        } else {
            if (focusTabAction) {
                tabs.find('> ul > li > a.active').removeClass("active");
                tabs.find('> ul > li#chat-tab-li-'+chat_id+' > a').addClass("active");
                tabs.find('> div.tab-content > div.active').removeClass('active');
                tabs.find('> div.tab-content > #chat-id-'+chat_id).addClass('active');
            } else {
                this.animateClick(chat_id);
            }
    		window.location.hash = '#/chat-id-'+chat_id;
        }
        
        ee.emitEvent('chatStartTab', [chat_id, {name: name, focus: (typeof focusTab !== 'undefined' ? focusTab : true), position: position}]);
    };

    this.backgroundChats = [];
    
    this.startChatBackground = function (chat_id,tabs,name,backgroundType) {
    	if ( this.chatUnderSynchronization(chat_id) == false ) {  
    		this.backgroundChats.push(parseInt(chat_id));
	    	var rememberAppend = this.disableremember == false ? '/(remember)/true' : '';

	    	if (!backgroundType) {
                backgroundType = 'background';
            }

	    	this.addTab(tabs, this.wwwDir +'chat/adminchat/'+chat_id+rememberAppend+'/(arg)/'+backgroundType, name, chat_id, false);
	    	ee.emitEvent('chatStartBackground', [chat_id,{name:name}]);
	    	return true;
    	} else {
            this.animateClick(chat_id);
        }
    	
    	return false;
    };
    
    this.protectCSFR = function()
    {
    	$('a.csfr-required').click(function(event) {

    		var inst = $(this);
    		if (!inst.attr('data-secured')){
        		inst.attr('href',inst.attr('href')+'/(csfr)/'+confLH.csrf_token);
        		inst.attr('data-secured',1);
        	}

            if (inst.hasClass('csfr-post') && !inst.hasClass('csfr-post-executed')) {

                event.preventDefault();
                event.stopPropagation();

                inst.addClass('csfr-post-executed');

                if (inst.attr('data-trans')) {
                    lhc.revealModal({'url': WWW_DIR_JAVASCRIPT + 'system/confirmdialog', 'backdrop':true,
                        'hidecallback' : function(){
                            inst.removeClass('csfr-post-executed');
                        },
                        'showcallback' : function(){
                        $('#confirm-button-action').click(function(event) {
                            $.post(inst.attr('href'), function(data) {
                                if (inst.attr('data-ajax-confirm') === 'true') {
                                    if (typeof data.error !== 'undefined' && data.error == false) {
                                        document.location.reload();
                                    } else {
                                        $('#confirm-dialog-content').html(data.result);
                                    }
                                } else {
                                    document.location.reload();
                                }
                            }).fail(function(e){
                                document.location.reload();
                            });
                        });
                    }});
                } else {
                    $.post(inst.attr('href'), function(){
                        document.location.reload();
                    }).fail(function(){
                        document.location.reload();
                    });
                }
            }
    	});
    };

    this.addSynchroChat = function (chat_id,message_id)
    {
        this.chatsSynchronising.push(chat_id);
        this.chatsSynchronisingMsg.push(chat_id + ',' +message_id);
        
        if (LHCCallbacks.addSynchroChat) {
        	LHCCallbacks.addSynchroChat(chat_id,message_id);
        }
    };

    this.removeSynchroChat = function (chat_id, passive)
    {
        var j = 0;

        while (j < this.chatsSynchronising.length) {

            if (this.chatsSynchronising[j] == chat_id) {

            this.chatsSynchronising.splice(j, 1);
            this.chatsSynchronisingMsg.splice(j, 1);

            } else { j++; }
        };

        this.forgetChat(chat_id,'achat_id');

        if (passive !== true) {
            ee.emitEvent('removeSynchroChat', [chat_id]);
        }

        if (LHCCallbacks.removeSynchroChat) {
        	LHCCallbacks.removeSynchroChat(chat_id);
        }

    };

    this.is_typing = false;
    this.typing_timeout = null;
   
    this.operatorTypingCallback = function(chat_id)
    {
    	var www_dir = this.wwwDir;
        var inst = this;
        
        if (inst.is_typing == false) {
            inst.is_typing = true;
            clearTimeout(inst.typing_timeout);
            
            if (inst.nodeJsMode == true) {
            	inst.typing_timeout = setTimeout(function(){inst.typingStoppedOperator(chat_id);},3000);
            	ee.emitEvent('operatorTyping', [{'chat_id':chat_id,'status':true}]);
            } else {                
                $.getJSON(www_dir + 'chat/operatortyping/' + chat_id+'/true',{ }, function(data){
                   inst.typing_timeout = setTimeout(function(){inst.typingStoppedOperator(chat_id);},3000);                   
                   if (LHCCallbacks.initTypingMonitoringAdmin) {
                   		LHCCallbacks.initTypingMonitoringAdmin(chat_id,true);
                   }                   
                }).fail(function(){
                	inst.typing_timeout = setTimeout(function(){inst.typingStoppedOperator(chat_id);},3000);
                });
            }
            
        } else {
             clearTimeout(inst.typing_timeout);
             inst.typing_timeout = setTimeout(function(){inst.typingStoppedOperator(chat_id);},3000);
        }        
    };
    
    this.initTypingMonitoringAdmin = function(chat_id) {
    	var inst = this;
        let editor = jQuery('#CSChatMessage-'+chat_id);

        if (editor.prop('nodeName') != 'LHC-EDITOR') {
            editor.bind('keyup', function (evt){
                inst.operatorTypingCallback(chat_id);
            });
        }
    };

    this.remarksTimeout = null;
    
    this.saveRemarks = function(chat_id) {
    	clearTimeout(this.remarksTimeout);
    	
    	$('#remarks-status-'+chat_id).addClass('text-warning');
    	$('#main-user-info-remarks-'+chat_id+' .alert').remove();
    	var inst = this;
    	this.remarksTimeout = setTimeout(function() {
    		$.postJSON(inst.wwwDir + 'chat/saveremarks/' + chat_id,{'data':$('#ChatRemarks-'+chat_id).val()}, function(data) {
				if (data.error == 'false') {
					$('#remarks-status-'+chat_id).removeClass('text-warning');
				} else {
					$('#main-user-info-remarks-'+chat_id).prepend(data.result);
				}
    		});
    	},500);
    };

    this.reaction = function(inst) {
        $.postJSON(this.wwwDir + 'chat/reaction/' + inst.attr('data-msg-id'), {'identifier' :inst.attr('data-identifier'), 'data': + inst.attr('data-value')}, function(data) {
            if (data.error == 'false') {
                $('#reaction-message-info-'+inst.attr('data-msg-id')).remove();
                $('#reaction-message-'+inst.attr('data-msg-id')).replaceWith(data.result);
            } else {
                alert(data.result);
            }
        });
    }
    
    this.saveNotes = function(chat_id) {
    	clearTimeout(this.remarksTimeout);    	    	
    	$('#remarks-status-online-'+chat_id).addClass('text-warning');
    	var inst = this;
    	this.remarksTimeout = setTimeout(function(){
    		$.postJSON(inst.wwwDir + 'chat/saveonlinenotes/' + chat_id,{'data':$('#OnlineRemarks-'+chat_id).val()}, function(data){
    			$('#remarks-status-online-'+chat_id).removeClass('text-warning');
            });
    	},500);    	
    };
    
    this.surveyShowed = false;

    this.typingStoppedOperator = function(chat_id) {
        var inst = this;
        if (inst.is_typing == true){
        	
        	if (lhinst.nodeJsMode  == true) {
        		inst.is_typing = false;
           		ee.emitEvent('operatorTyping', [{'chat_id':chat_id,'status':false}]);
            } else {        	
	            $.getJSON(this.wwwDir + 'chat/operatortyping/' + chat_id+'/false',{ }, function(data){
	                inst.is_typing = false;                
	                if (LHCCallbacks.initTypingMonitoringAdmin) {
	               		LHCCallbacks.initTypingMonitoringAdmin(chat_id,false);
	                };
	            }).fail(function(){
	            	inst.is_typing = false;
	            });
            }
        }
    };

    this.refreshFootPrint = function(inst) {
    	inst.addClass('disabled');
    	$.get(this.wwwDir + 'chat/chatfootprint/' + inst.attr('rel'),{ }, function(data){
    		$('#footprint-'+inst.attr('rel')).html(data);
    		inst.removeClass('disabled');
    	});
    };

    this.makeAbstractRequest = function(chat_id, inst) { 
    	$.get(inst.attr('href'), function(data) {
    		lhinst.syncadmininterfacestatic();	
    		
			if (LHCCallbacks.userRedirectedSurvey) {
	       		LHCCallbacks.userRedirectedSurvey(chat_id);
			};
			
    	});
    	return false;
    };
    
    this.refreshOnlineUserInfo = function(inst) {
    	 inst.addClass('disabled');
    	 $.get(this.wwwDir + 'chat/refreshonlineinfo/' + inst.attr('rel'),{ }, function(data){
    		 $('#online-user-info-'+inst.attr('rel')).html(data);
    		 inst.removeClass('disabled');
         });
    };

    this.processCollapse = function(chat_id)
    {
    	if ($('#chat-main-column-'+chat_id+' .collapse-right').text() == 'chevron_right'){
	    	$('#chat-right-column-'+chat_id).hide();
	    	$('#chat-main-column-'+chat_id).removeClass('col-md-8').addClass('col-md-12');
	    	$('#chat-main-column-'+chat_id+' .collapse-right').text('chevron_left');
	    	try {
		    	if (localStorage) {
					localStorage.setItem('lhc_rch',1);				
				}
	    	} catch(e) {}
    	} else {
    		$('#chat-right-column-'+chat_id).show();
	    	$('#chat-main-column-'+chat_id).removeClass('col-md-12').addClass('col-md-8');
	    	$('#chat-main-column-'+chat_id+' .collapse-right').text('chevron_right');
	    	
	    	try {
		    	if (localStorage) {
					localStorage.removeItem('lhc_rch');				
				}
	    	} catch(e) {}
    	};
    };

    this.chatUnderSynchronization = function(chat_id)
    {
        var j = 0;

        while (j < this.chatsSynchronising.length) {

            if (this.chatsSynchronising[j] == chat_id) {

            return true;

            } else { j++; }
        }

        return false;
    };

    this.getChatIndex = function(chat_id)
    {
        var j = 0;

        while (j < this.chatsSynchronising.length) {

            if (this.chatsSynchronising[j] == chat_id) {

            return j;

            } else { j++; }
        }

        return false;
    };

	this.closeActiveChatDialog = function(chat_id, tabs, hidetab, ignoreRelated)
	{
	    var that = this;
        var attribute = '{}';
        let editor = $('#CSChatMessage-'+chat_id);

        editor.length != 0 && (attribute = editor.attr('related-actions'));

        if (editor.length != 0 && editor.attr('close-related') !== "false" && !ignoreRelated) {
            var clsAction = $('#chat-close-action-'+chat_id);
            clsAction.find('.close-text').text(clsAction.attr('data-loading')).parent().find('.material-icons').text('sync').addClass('lhc-spin');
            lhc.revealModal({
                'url': WWW_DIR_JAVASCRIPT+'chat/relatedactions/'+chat_id,
                'loadmethod' : 'post',
                'datapost' : attribute,
                'backdrop': true,
                'hidecallback' : function() {
                    clsAction.find('.close-text').text(clsAction.find('.close-text').attr('data-original')).parent().find('.material-icons').text('close').removeClass('lhc-spin');
                },
                'on_empty': function() {
                    that.closeActiveChatDialog(chat_id, tabs, hidetab, true); // If no related actions close it instantly
                }
            });
            return;
        }

        ee.emitEvent('angularSyncDisabled', [true]);
	    $.postJSON(this.wwwDir + this.closechatadmin + chat_id, attribute, function (data) {
            ee.emitEvent('angularSyncDisabled', [false]);
	        if (data.error == false) {
                ee.emitEvent('angularLoadChatList');
            } else {
	            alert(data.result);
                ee.emitEvent('angularStartChatbyId',[chat_id]);
            }

            $('#myModal').modal('hide');

            if (hidetab == true && that.closeWindowOnChatCloseDelete == true) {
                window.close();
            }

        }).fail(function(jqXHR, textStatus, errorThrown) {
            ee.emitEvent('angularSyncDisabled', [false]);
            alert('There was an error processing your request: ' + '[' + jqXHR.status + '] [' + jqXHR.statusText + '] [' + jqXHR.responseText + '] ' + errorThrown);
            ee.emitEvent('angularStartChatbyId',[chat_id]);
        });

        if (editor.length != 0 && editor.prop('nodeName') != 'LHC-EDITOR') {
            editor.unbind('keydown', function(){});
            editor.unbind('keyup', function(){});
        };

        if (!!window.postMessage && window.opener) {
            window.opener.postMessage("lhc_ch:chatclosed:"+chat_id, '*');
        };

        that.channel && that.channel.postMessage({'action':'close_chat','args':{'chat_id' : chat_id}});

        that.removeSynchroChat(chat_id);

        if (hidetab == true) {

            var location = that.smartTabFocus(tabs, chat_id);

            setTimeout(function() {
                window.location.hash =  location;
            },500);
        };

        if (LHCCallbacks.chatClosedCallback) {
            LHCCallbacks.chatClosedCallback(chat_id);
        };
	};

	this.smartTabFocus = function(tabs, chat_id, params) {
		var index = tabs.find('> ul > #chat-tab-li-'+chat_id).index();

		if (!params) {params = {};}

		var navigationDirection = (params.up == true || typeof params.up == 'undefined') ? 1 : -1;

		if (!params['keep']) {
            tabs.find('> ul > #chat-tab-li-'+chat_id).remove();
            tabs.find('#chat-id-'+chat_id).remove();
        } else {
            tabs.find('> ul > li > a.active').removeClass('active');
        }

    	var linkTab = tabs.find('> ul > li:eq('+ (index - navigationDirection)+')');

    	if (linkTab.attr('id') !== undefined) {
    		var link = linkTab.find('> a');
    	} else {
    		linkTabRight = tabs.find('> ul > li:eq('+ (index) + ')');
    		if (linkTabRight.length > 0) {
    			var link = linkTabRight.find('> a');
    		} else {
    			var link = linkTab.find('> a');
    		}
    	}

    	if (!tabs.find('> ul > li > a.active').length) {

    	    var moveLeft = true;
    	    var navigator = 1;
    	    while (moveLeft) {
    	        if (!link.hasClass('non-focus')) {
                    moveLeft = false;
                } else {
                    moveLeft = true;
                    var prevElement = link.parent().prev();
                    if (prevElement.find(' > a').length) {
                        link = prevElement.find(' > a');
                    }
                }
            }

    		link.tab('show');

    		if (link.attr('id') !== undefined) {
        		var new_chat_id = link.attr('href').replace('#chat-id-','');
        		this.removeBackgroundChat(new_chat_id);
        		this.hideNotification(new_chat_id);
        		if (!params['keep']) {
                    ee.emitEvent('chatTabFocused', [new_chat_id]);
                }

        	}
    	}

    	if (link.attr('href') !== undefined) {
            return link.attr('href').replace('#','#/');
        } else {
    	    return '#';
        }
	};

	this.startChatCloseTabNewWindow = function(chat_id, tabs, name)
	{
		window.open(this.wwwDir + 'chat/single/'+chat_id,'chatwindow-chat-id-'+chat_id,"menubar=1,resizable=1,width=800,height=650");

    	this.smartTabFocus(tabs, chat_id);

        if (this.closeWindowOnChatCloseDelete == true)
        {
            window.close();
        };

        this.removeSynchroChat(chat_id);
	    this.syncadmininterfacestatic();

	    return false;
	};

	this.removeDialogTab = function(chat_id, tabs, hidetab)
	{
        this.channel && this.chatUnderSynchronization(chat_id) === true && this.channel.postMessage({'action':'close_chat','args':{'chat_id' : chat_id}});

        let editor = $('#CSChatMessage-'+chat_id);

	    if (editor.length != 0 && editor.prop('nodeName') != 'LHC-EDITOR') {
            editor.unbind('keydown', function(){});
            editor.unbind('keyup', function(){});
	    }

	    this.removeSynchroChat(chat_id);

	    if (hidetab == true) {

	    	var location = this.smartTabFocus(tabs, chat_id);

	    	setTimeout(function() {
	    		window.location.hash = location;
	    	},500);

	        if (this.closeWindowOnChatCloseDelete == true)
	        {
	            window.close();
	        };
	    };

	    this.syncadmininterfacestatic();
	};

	this.removeActiveDialogTag = function(tabs) {

		/* @todo add removement of current active tab */

        if (this.closeWindowOnChatCloseDelete == true)
        {
            window.close();
        };
	};

	this.deleteChat = function(chat_id, tabs, hidetab)
	{
        if (confirm(confLH.transLation.delete_confirm)) {

            var that = this;

            $.postJSON(this.wwwDir + this.deletechatadmin + chat_id, function(data){
                if (data.error == true) {
                    alert(data.result);
                } else {

                    let editor = $('#CSChatMessage-'+chat_id);

                    if (editor.length != 0 && editor.prop('nodeName') != 'LHC-EDITOR') {
                        editor.unbind('keydown', function(){});
                        editor.unbind('keyup', function(){});
                    }

                    that.removeSynchroChat(chat_id);

                    if (hidetab == true) {

                        var location = that.smartTabFocus(tabs, chat_id);

                        setTimeout(function() {
                            window.location.hash = location;
                        },500);

                        if (that.closeWindowOnChatCloseDelete == true)
                        {
                            window.close();
                        }
                    };

                    if (LHCCallbacks.chatDeletedCallback) {
                        LHCCallbacks.chatDeletedCallback(chat_id);
                    };

                    that.syncadmininterfacestatic();
                }

            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.dir(jqXHR);
                alert('getJSON request failed! ' + textStatus + ':' + errorThrown + ':' + jqXHR.responseText);
            });
        }
	};

	this.rejectPendingChat = function(chat_id, tabs)
	{
	    var that = this;
	    $.postJSON(this.wwwDir + this.deletechatadmin + chat_id ,{}, function(data){
            that.syncadmininterfacestatic();
	    }).fail(function(jqXHR, textStatus, errorThrown) {
            console.dir(jqXHR);
            alert('getJSON request failed! ' + textStatus + ':' + errorThrown + ':' + jqXHR.responseText);
        });
	};

	this.startChatNewWindow = function(chat_id,name)
	{
	    var popupWindow = window.open(this.wwwDir + 'chat/single/'+chat_id,'chatwindow-chat-id-'+chat_id,"menubar=1,resizable=1,width=800,height=650");

	    if (popupWindow !== null) {
            popupWindow.focus();
            var inst = this;
            setTimeout(function(){
                inst.syncadmininterfacestatic();
            },1000);

            ee.emitEvent('chatStartOpenWindow', [chat_id]);
        }
	};

    this.startMailNewWindow = function(chat_id,name)
    {
        window.open(this.wwwDir + 'mailconv/single/'+chat_id,'mailwindow-chat-id-'+chat_id,"menubar=1,resizable=1,width=900,height=650").focus();
        var inst = this;
        setTimeout(function(){
            inst.syncadmininterfacestatic();
        },1000);
    };


    this.startChatNewWindowArchive = function(archive_id, chat_id,name)
    {
        var popupWindow = window.open(this.wwwDir + 'chatarchive/viewarchivedchat/' + archive_id + '/' + chat_id + '/(mode)/popup','chatwindow-chat-id-'+chat_id,"menubar=1,resizable=1,width=800,height=650");
        if (popupWindow !== null) {
            popupWindow.focus();
            ee.emitEvent('chatStartOpenWindowArchive', [archive_id, chat_id]);
        }
    };

	this.speechToText = function(chat_id)
	{
		if (this.speechHandler == false)
		{
			this.speechHandler = new LHCSpeechToText();
		}

		this.speechHandler.listen({'chat_id':chat_id});

	};

	this.startChatTransfer = function(chat_id,tabs,name,transfer_id, background) {
		var inst = this;
	    $.getJSON(this.wwwDir + this.accepttransfer + transfer_id ,{}, function(data) {

	        if (data.scope == 1) {
                inst.startMailChat(chat_id,tabs,name);
            } else {
                if ($('#chat-tab-link-' + chat_id).length == 0) {
                    if (background) {
                        inst.removeSynchroChat(chat_id);
                        inst.startChatBackground(chat_id,tabs,name)
                    } else {
                        inst.startChat(chat_id,tabs,name);
                    }
                } else {
                    inst.updateVoteStatus(chat_id);
                }
            }

	    	if (LHCCallbacks.operatorAcceptedTransfer) {
	       		LHCCallbacks.operatorAcceptedTransfer(chat_id);
	    	};

	    }).fail(function(){
	    	inst.startChat(chat_id,tabs,name);
	    });
	};

	this.startChatNewWindowTransfer = function(chat_id, name, transfer_id, transfer_scope)
	{
		$.getJSON(this.wwwDir + this.accepttransfer + transfer_id ,{}, function(data){
			if (LHCCallbacks.operatorAcceptedTransfer) {
	       		LHCCallbacks.operatorAcceptedTransfer(chat_id);
	    	};
		});

		if (transfer_scope == 1) {
            return this.startMailNewWindow(chat_id,name);
        } else {
            return this.startChatNewWindow(chat_id,name);
        }
	};

	this.startChatNewWindowTransferByTransfer = function(chat_id, nt, transferScope, background)
	{
		var inst = this;
		$.ajax({
	        type: "GET",
	        url: this.wwwDir + this.accepttransfer + chat_id+'/(mode)/chat/(scope)/' + transferScope,
	        cache: false,
	        dataType: 'json'
	    }).done(function(data){

	    	if ($('#tabs').length > 0) {
    			if (transferScope == 1) {
                    inst.startMailChat(data.chat_id, $('#tabs'), nt);
                    window.focus();
                } else {
                    if (typeof background !== 'undefined' && background === true) {
                        inst.addOpenTrace('transfer_open_background');
                        inst.startChatBackground(data.chat_id, $('#tabs'), nt);
                    } else {
                        window.focus();
                        inst.addOpenTrace('transfer_open');
                        inst.startChat(data.chat_id, $('#tabs'), nt);
                    }
    			}
    		} else {
                if (transferScope == 1) {
                    inst.startMailNewWindow(data.chat_id,'ChatMail');
                } else {
    			    inst.startChatNewWindow(data.chat_id,'');
                }
    		}

	    	if (LHCCallbacks.operatorAcceptedTransfer) {
	       		LHCCallbacks.operatorAcceptedTransfer(data.chat_id);
	    	};
	    });

	    this.syncadmininterfacestatic();
        return false;
	};

	this.switchLang = function(form,lang){
		var languageAppend = '<input type="hidden" value="'+lang+'" name="switchLang" />';
		form.append(languageAppend);
		form.submit();

		return false;
	};

	this.sendLinkToMail = function( embed_code,file_id) {
		var val = window.parent.$('#MailMessage').val();
		window.parent.$('#MailMessage').val(((val != '') ? val+"\n" : val)+embed_code);
		$('#embed-button-'+file_id).addClass('btn-success');
	};

	this.sendLinkToEditor = function(chat_id, embed_code,file_id) {
        let editor = window.parent.$('#CSChatMessage-'+chat_id);
        if (editor.prop('nodeName') == 'LHC-EDITOR') {
            editor[0].insertContent(embed_code,{"new_line":true,"convert_bbcode" : true});
        } else {
            var val = editor.val();
            editor.val(((val != '') ? val+"\n" : val)+embed_code);
            $('#embed-button-'+file_id).addClass('btn-success');
        }
    };

	this.sendLinkToGeneralEditor = function(embed_code,file_id, params) {
	    var editor = window.parent.$('.embed-into');
        
        if (editor.length == 0) {
            editor = window.opener.$('.embed-into');
        };

        if (typeof params !== 'undefined' && typeof params['replace'] !== `undefined` && params['replace'] == true){
            if (editor.prop('nodeName') == 'LHC-EDITOR') {
                editor[0].setContent(embed_code,{"convert_bbcode" : true});
            } else {
                editor.val(embed_code);
            }
        } else {
            if (editor.prop('nodeName') == 'LHC-EDITOR') {
                editor[0].insertContent(embed_code,{"new_line":true,"convert_bbcode" : true});
            } else {
                var val = editor.val();
                editor.val(((val != '') ? val+"\n" : val)+embed_code);
            }
        }

		$('#embed-button-'+file_id).addClass('btn-success');
	};

    this.hideOnTransferHappen = function(chat_id){
        var inst = this;
        var intervalChecker = setInterval( function() {
            if (parseInt(confLH.user_id) != parseInt($('#chat-owner-'+chat_id).attr('user-id'))) {
                if ($('#tabs').length > 0) {
                    inst.removeDialogTab(chat_id,$('#tabs'),true);
                    inst.channel && inst.channel.postMessage({'action':'close_chat','args':{'chat_id' : parseInt(chat_id)}});
                }
                clearInterval(intervalChecker);
            }
        },1000);
    };

	this.hideTransferModal = function(chat_id, obj)
	{
		var inst = this;
        setTimeout(function(){
            $('#myModal').modal('hide');
            if ($('#tabs').length > 0) {
                if (obj === 'mail') {
                    inst.removeDialogTabMail('mc'+chat_id,$('#tabs'),true)
                } else {
                    inst.hideOnTransferHappen(chat_id);
                }
            } else {
                if (obj === 'mail') {
                    ee.emitEvent('mailChatModified', [chat_id]);
                }
            }
        },1000);
	};

    this.transferChatDep = function(chat_id, obj)
    {
        $('.transfer-action-button').attr('disabled','disabled');
        var inst = this;
        var user_id = $('[name=DepartamentID'+chat_id+']:checked').val();
        $.postJSON(this.wwwDir + this.trasnsferuser + chat_id + '/' + user_id ,{'type':'dep', 'obj' : obj}, function(data){
            if (data.error == 'false') {
                $('#transfer-block-'+data.chat_id).html(data.result);
                inst.hideTransferModal(chat_id, obj);
            } else {
                $('#transfer-block-'+chat_id).text(JSON.stringify(data));
            };
            $('.transfer-action-button').removeAttr('disabled');
        }).fail(function(respose) {
            var escaped = '<div style="margin:10px 10px 30px 10px;" class="alert alert-warning" role="alert">' + $("<div>").text('You have weak internet connection or the server has problems. Try to refresh the page or send the message again.' + (typeof respose.status !== 'undefined' ? ' Error code ['+respose.status+']' : '') + (typeof respose.responseText !== 'undefined' ? respose.responseText : '')).html() + '</div>';
            $('#transfer-block-'+chat_id).html(escaped);
            $('.transfer-action-button').removeAttr('disabled');
        });
    };

	this.transferChat = function(chat_id, obj)
	{
        var inst = this;

		var user_id = $('[name=TransferTo'+chat_id+']:checked').val();

        $('.transfer-action-button').attr('disabled','disabled');
        $.postJSON(this.wwwDir + this.trasnsferuser + chat_id + '/' + user_id ,{'type':'user', 'obj': obj}, function(data){
			if (data.error == 'false') {
				$('#transfer-block-'+data.chat_id).html(data.result);
                inst.hideTransferModal(chat_id, obj);
			} else {
                $('#transfer-block-'+chat_id).text(JSON.stringify(data));
            };
            $('.transfer-action-button').removeAttr('disabled');
		}).fail(function(respose) {
            var escaped = '<div style="margin:10px 10px 30px 10px;" class="alert alert-warning" role="alert">' + $("<div>").text('You have weak internet connection or the server has problems. Try to refresh the page or send the message again.' + (typeof respose.status !== 'undefined' ? ' Error code ['+respose.status+']' : '') + (typeof respose.responseText !== 'undefined' ? respose.responseText : '')).html() + '</div>';
            $('#transfer-block-'+chat_id).html(escaped);
            $('.transfer-action-button').removeAttr('disabled');
        });
	};

	this.changeOwner = function(chat_id, obj) {
        var inst = this;
        var user_id = $('#id_new_user_id').val();
        $('.transfer-action-button').attr('disabled','disabled');
        $.postJSON(this.wwwDir + this.trasnsferuser + chat_id + '/' + user_id, {'type' : 'change_owner','obj' : obj}, function(data){
            if (data.error == 'false') {
                $('#transfer-block-'+data.chat_id).html(data.result);
                inst.hideTransferModal(chat_id, obj);
            } else {
                $('#transfer-block-'+chat_id).text(JSON.stringify(data));
            };
            $('.transfer-action-button').removeAttr('disabled');
        }).fail(function(respose) {
            var escaped = '<div style="margin:10px 10px 30px 10px;" class="alert alert-warning" role="alert">' + $("<div>").text('You have weak internet connection or the server has problems. Try to refresh the page or send the message again.' + (typeof respose.status !== 'undefined' ? ' Error code ['+respose.status+']' : '') + (typeof respose.responseText !== 'undefined' ? respose.responseText : '')).html() + '</div>';
            $('#transfer-block-'+chat_id).html(escaped);
            $('.transfer-action-button').removeAttr('disabled');
        });
    };

	this.changeDep = function(chat_id, obj) {
        var inst = this;
        var user_id = $('#id_new_dep_id').val();
        $('.transfer-action-button').attr('disabled','disabled');
        $.postJSON(this.wwwDir + this.trasnsferuser + chat_id + '/' + user_id, {'type':'change_dep','obj' : obj}, function(data){
            if (data.error == 'false') {
                $('#transfer-block-'+data.chat_id).html(data.result);
                $('#myModal').modal('hide');
                if (obj === 'mail') {
                    ee.emitEvent('mailChatModified', [chat_id]);
                } else {
                    inst.updateVoteStatus(chat_id);
                }
            } else {
                $('#transfer-block-'+chat_id).text(JSON.stringify(data));
            };
            $('.transfer-action-button').removeAttr('disabled');
        }).fail(function(respose) {
            var escaped = '<div style="margin:10px 10px 30px 10px;" class="alert alert-warning" role="alert">' + $("<div>").text('You have weak internet connection or the server has problems. Try to refresh the page or send the message again.' + (typeof respose.status !== 'undefined' ? ' Error code ['+respose.status+']' : '') + (typeof respose.responseText !== 'undefined' ? respose.responseText : '')).html() + '</div>';
            $('#transfer-block-'+chat_id).html(escaped);
            $('.transfer-action-button').removeAttr('disabled');
        });

    };

	this.chooseSurvey = function(chat_id)
	{
		var survey_id = $('[name=SurveyItem'+chat_id+']:checked').val();

		$.postJSON(this.wwwDir + "survey/choosesurvey/" + chat_id + '/' + survey_id, function(data){
			if (data.error == 'false') {
				$('#survey-block-'+data.chat_id).html(data.result);
			};
		});
	};

	this.redirectContact = function(chat_id,message){
		if (typeof message === 'undefined' || confirm(message)){
			$.postJSON(this.wwwDir + 'chat/redirectcontact/' + chat_id, function(data){
				lhinst.syncadmininterfacestatic();
				if (LHCCallbacks.userRedirectedContact) {
		       		LHCCallbacks.userRedirectedContact(chat_id);
				};
			});
		}
	};

	this.redirectToURL = function(chat_id) {
        lhc.revealModal({'url':WWW_DIR_JAVASCRIPT+'chat/singleaction/'+chat_id + '/redirecttourl'});
	};

    this.setAreaAttrByCheckbox = function(chat_id, action) {

        var array = [];
        var checkboxes = document.querySelectorAll('input[name='+action+'-'+chat_id+']:checked');

        for (var i = 0; i < checkboxes.length; i++) {
            array.push(checkboxes[i].value);
        }

        var attribute = $('#CSChatMessage-'+chat_id).attr('related-actions');

        var attributeSet = {};

        if (attribute) {
            attributeSet = JSON.parse(attribute);
        }

        attributeSet[action] = array;

        $('#CSChatMessage-'+chat_id).attr('related-actions', JSON.stringify(attributeSet));
    }

    this.explandCollapse = function (type, object_id, provider) {
        var expandIcon = document.getElementById('expand-action-' + type + '-' + object_id);
        expandIcon.innerText = expandIcon.innerText == 'expand_less' ? 'expand_more' : 'expand_less';
        document.getElementById('lhc-list-' + type + '-' + object_id).style.display = expandIcon.innerText == 'expand_less' ? 'block' : 'none';

        // Load if data not loaded yet
        if (expandIcon.getAttribute('data-loaded') == 'false') {
            expandIcon.setAttribute('data-loaded','true');
            $.postJSON(this.wwwDir + provider, function(data) {
                var elm = document.getElementById('lhc-list-' + type + '-' + object_id);

                if (!elm) {
                    return;
                }

                elm.innerHTML = data.data;
            });
        }
    };
    
	this.redirectToURLOnline = function(online_user_id,trans) {
		var url = prompt(trans, "");
		if (url != null) {
			lhinst.addRemoteOnlineCommand(online_user_id,'lhc_chat_redirect:'+url.replace(new RegExp(':','g'),'__SPLIT__'));
			lhinst.addExecutionCommand(online_user_id,'lhc_cobrowse_multi_command__lhc_chat_redirect:'+url.replace(new RegExp(':','g'),'__SPLIT__'));
		}
	};

	this.chatTabsOpen = function ()
	{
	    window.open(this.wwwDir + 'chat/chattabs/','chatwindows',"menubar=1,resizable=1,width=800,height=650");
	    return false;
	};

	this.explicitClose = false;

	this.sendCannedMessage = function(chat_id,link_inst)
	{
		if ($('#id_CannedMessage-'+chat_id).val() > 0) {
			link_inst.addClass('secondary');
			var delayMiliseconds = parseInt($('#id_CannedMessage-'+chat_id).find(':selected').attr('data-delay'))*1000;
			var www_dir = this.wwwDir;
			var inst  = this;
			if (inst.is_typing == false) {
	            inst.is_typing = true;
	            clearTimeout(inst.typing_timeout);

	            if (LHCCallbacks.initTypingMonitoringAdminInform) {
               		LHCCallbacks.initTypingMonitoringAdminInform({'chat_id':chat_id,'status':true});
                };

	            $.getJSON(www_dir + 'chat/operatortyping/' + chat_id+'/true',{ }, function(data){
	               if (LHCCallbacks.initTypingMonitoringAdmin) {
                   		LHCCallbacks.initTypingMonitoringAdmin(chat_id,true);
                   };

	               inst.typing_timeout = setTimeout(function(){inst.typingStoppedOperator(chat_id);link_inst.removeClass('secondary');},(delayMiliseconds > 3000 ? delayMiliseconds : 3000));
	            }).fail(function(){
	            	inst.typing_timeout = setTimeout(function(){inst.typingStoppedOperator(chat_id);},3000);
	            });
	        } else {
	             clearTimeout(inst.typing_timeout);
	             inst.typing_timeout = setTimeout(function(){inst.typingStoppedOperator(chat_id);},3000);
	             link_inst.removeClass('secondary');
	        };
	        if (delayMiliseconds > 0) {
	        	setTimeout(function(){
	        		var pdata = {
		    				msg	: $('#id_CannedMessage-'+chat_id).find(':selected').attr('data-msg')
		    		};

                    if ($('#CSChatMessage-'+chat_id).attr('mode-write')) {
                        pdata.mode_write = $('#CSChatMessage-'+chat_id).attr('mode-write');
                    }

                    let editor = $('#CSChatMessage-'+chat_id);

                    if (editor.prop('nodeName') == 'LHC-EDITOR') {
                        editor[0].setContent('');
                    } else {
                        editor.val('');
                    }

		    		$.postJSON(www_dir + inst.addmsgurl + chat_id, pdata , function(data){
		    			if (LHCCallbacks.addmsgadmin) {
		            		LHCCallbacks.addmsgadmin(chat_id);
		            	};
		            	ee.emitEvent('chatAddMsgAdmin', [chat_id]);
		    			lhinst.syncadmincall();
		    			return true;
		    		});
	        	},delayMiliseconds);
	        } else {
	        	var pdata = {
	    				msg	: $('#id_CannedMessage-'+chat_id).find(':selected').attr('data-msg')
	    		};
                if ($('#CSChatMessage-'+chat_id).attr('mode-write')) {
                    pdata.mode_write = $('#CSChatMessage-'+chat_id).attr('mode-write');
                }
                let editor = $('#CSChatMessage-'+chat_id);

                if (editor.prop('nodeName') == 'LHC-EDITOR') {
                    editor[0].setContent('');
                } else {
                    editor.val('');
                }

	    		$.postJSON(this.wwwDir + this.addmsgurl + chat_id, pdata , function(data){
	    			if (LHCCallbacks.addmsgadmin) {
	            		LHCCallbacks.addmsgadmin(chat_id);
	            	};
	            	ee.emitEvent('chatAddMsgAdmin', [chat_id]);
	    			lhinst.syncadmincall();
	    			return true;
	    		});
	        }
		};
		return false;
	};

	this.theme = null;

	this.chatStatus = null;

	this.survey = null;

	this.isBlinking = false;

	this.startBlinking = function(){
		if (this.isBlinking == false) {
        	var inst = this;
            var newExcitingAlerts = (function () {
            	  var oldTitle = document.title;
            	  var msg = "!!! "+document.title;
            	  var timeoutId;
            	  var blink = function() { document.title = document.title == msg ? ' ' : msg; };
            	  var clear = function() {
            	    clearInterval(timeoutId);
            	    document.title = oldTitle;
            	    window.onmousemove = null;
            	    timeoutId = null;
            	    inst.isBlinking = false;
            	  };
            	  return function () {
            	    if (!timeoutId) {
            	      timeoutId = setInterval(blink, 1000);
            	      window.onmousemove = clear;
            	    }
            	  };
            }());
            newExcitingAlerts();
            this.isBlinking = true;
        };
	};

	this.playNewMessageSound = function() {

	    if (Modernizr.audio && this.audio !== null) {
    	    this.audio.src = Modernizr.audio.ogg ? WWW_DIR_JAVASCRIPT_FILES + '/new_message.ogg?v=3' :
                        Modernizr.audio.mp3 ? WWW_DIR_JAVASCRIPT_FILES + '/new_message.mp3?v=3' : WWW_DIR_JAVASCRIPT_FILES + '/new_message.wav?v=3';
    	    this.audio.load();
	    };

	    if(!$("textarea[name=ChatMessage]").is(":focus")) {
	    	this.startBlinking();
    	};
	};

	this.playInvitationSound = function() {
		if (Modernizr.audio && this.audio !== null) {
    	    this.audio.src = Modernizr.audio.ogg ? WWW_DIR_JAVASCRIPT_FILES + '/invitation.ogg' :
                        Modernizr.audio.mp3 ? WWW_DIR_JAVASCRIPT_FILES + '/invitation.mp3' : WWW_DIR_JAVASCRIPT_FILES + '/invitation.wav';
    	    this.audio.load();
	    }
	};

	this.playPreloadSound = function() {
		if (Modernizr.audio && this.audio !== null) {
			this.audio.src = Modernizr.audio.ogg ? WWW_DIR_JAVASCRIPT_FILES + '/silence.ogg' :
				Modernizr.audio.mp3 ? WWW_DIR_JAVASCRIPT_FILES + '/silence.mp3' : WWW_DIR_JAVASCRIPT_FILES + '/silence.wav';
            this.audio.load();
	    }
	};

    this.scrollLoading = false;
    this.scrollPending = false;

	this.loadPreviousMessages = function (inst, noScroll) {
        if (this.scrollLoading == false) {
            this.scrollLoading = true;
            var _that = this;
            inst.find('.material-icons').addClass('lhc-spin');
            $.getJSON(this.wwwDir + 'chat/loadpreviousmessages/' + inst.attr('chat-id') + '/' + inst.attr('message-id') + '/(initial)/' + inst.attr('data-initial') + '/(original)/' + inst.attr('chat-original-id'), function(data) {
                if (data.error == false) {

                    inst.find('.material-icons').removeClass('lhc-spin');

                    inst.attr('data-initial',0);

                    var msg = $('#messagesBlock-'+inst.attr('chat-original-id'));

                    var scrollHeight = msg[0].scrollHeight;
                    var currentScroll = msg.scrollTop();

                    msg.prepend(data.result);

                    var newScrollHeight = msg[0].scrollHeight;
                    var scrollDiff = newScrollHeight - scrollHeight;

                    if (inst.attr('auto-scroll') == 1) {
                        inst.attr('auto-scroll',0);
                        msg.scrollTop(msg.prop('scrollHeight'));
                    } else if (!noScroll) {
                        var elm = document.getElementById('scroll-to-chat-' + inst.attr('chat-id') + '-' + inst.attr('message-id'));
                        if (elm) {
                            msg[0].scrollTop = elm.offsetTop;
                        }
                    } else {
                        // Maintain relative scroll position after content is prepended
                        msg.scrollTop(currentScroll + scrollDiff);
                    }

                    if (data.has_messages == true) {
                        inst.attr('message-id', data.message_id);
                        inst.attr('chat-id',data.chat_id);

                        _that.scrollLoading = false;

                        if (_that.scrollPending == true) {
                            _that.scrollPending = false;
                            _that.loadPreviousMessages(inst, noScroll);
                        }

                    } else {
                        inst.remove();
                        _that.scrollLoading = false;
                        _that.scrollPending = false;
                    }

                } else {
                    _that.scrollLoading = false;
                    _that.scrollPending = false;
                }
            });
        } else {
            this.scrollPending = true;
        }

    };

	this.hidenicknamesstatus = null;

	this.onScrollAdmin = function(chat_id)
    {
        var messageBlock = $('#messagesBlock-'+chat_id);
        var scrollHeight = messageBlock.prop("scrollHeight");
        var isAtTheBottom = Math.abs((scrollHeight - messageBlock.prop("scrollTop")) - messageBlock.prop("clientHeight"));

        if (isAtTheBottom > 20) {
            $('#scroll-button-admin-'+chat_id).removeClass('d-none');
        } else {
            $('#scroll-button-admin-'+chat_id).addClass('d-none').find('> button').text($('#scroll-button-admin-'+chat_id+' > button').attr('data-default'));
        }
    }

    this.scrollToTheBottomMessage = function(chat_id)
    {
        var unreadSeparator = $('#unread-separator-'+chat_id);
        if (unreadSeparator.length > 0) {
            unreadSeparator[0].scrollIntoView();
            setTimeout(function(){
                unreadSeparator.remove();
            },1000);
        } else {
            var messagesBlock = $('#messagesBlock-'+chat_id);
            messagesBlock.scrollTop(messagesBlock.prop('scrollHeight'));
        }
    }

    this.syncadmincall = function()
	{
	    if (this.chatsSynchronising.length > 0)
	    {
	        if (this.underMessageAdd == false && this.syncroRequestSend == false)
	        {
	            this.syncroRequestSend = true;

        	    $.postJSON(this.wwwDir + this.syncadmin ,{ 'chats[]': this.chatsSynchronisingMsg }, function(data){

                    if (typeof data.error_url !== 'undefined') {
                        document.location.replace(data.error_url);
                    }

        	    	try {
	        	        // If no error
	        	        if (data.error == 'false')
	        	        {
	        	            if (data.result != 'false')
	        	            {
	        	            	var playSound = false

	        	                $.each(data.result,function(i,item) {

	        	                	  var messageBlock = $('#messagesBlock-'+item.chat_id);
	        	                	  var scrollHeight = messageBlock.prop("scrollHeight");
	        	                	  var isAtTheBottom = Math.abs((scrollHeight - messageBlock.prop("scrollTop")) - messageBlock.prop("clientHeight"));

	        	                	  messageBlock.find('.pending-storage').slice(0, item.mn).remove();


                                    var mainElement = $('#chat-tab-link-'+item.chat_id);

                                    var needUnreadSeparator = !focused;

                                    if (!mainElement.hasClass('active')) {
                                        if (mainElement.find('span.msg-nm').length > 0) {
                                            var totalMsg = (parseInt(mainElement.find('span.msg-nm').attr('rel')) + item.mn);
                                            mainElement.find('span.msg-nm').html(' (' + totalMsg + ')' ).attr('rel',totalMsg);
                                        } else {
                                            needUnreadSeparator = true;
                                            mainElement.append('<span rel="'+item.mn+'" class="msg-nm"> ('+item.mn+')</span>');
                                            mainElement.addClass('has-pm');
                                        }
                                    }

                                    if (isAtTheBottom > 20) {
                                        needUnreadSeparator = true;
                                        $('#scroll-button-admin-'+item.chat_id+' > button').text($('#scroll-button-admin-'+item.chat_id+' > button').attr('data-new'));
                                    }

                                    if (needUnreadSeparator == true && document.getElementById('unread-separator-'+item.chat_id) === null) {
                                        item.content = item.content.replace('<span class="usr-tit','<div id="unread-separator-'+item.chat_id+'" class="new-msg-holder border-bottom border-danger text-center"><span class="new-msg bg-danger text-white d-inline-block fs12 rounded-top">'+confLH.transLation.new+'</span></div><span class="usr-tit');
                                    }

                                    messageBlock.append(item.content);
                                    messageBlock.find('.pending-storage').appendTo(messageBlock);

	        	                	  lhinst.addQuateHandler(item.chat_id);

	        	                	  if (isAtTheBottom < 20) {
	        	                		  messageBlock.scrollTop(scrollHeight);
	        	                	  }

	        		                  lhinst.updateChatLastMessageID(item.chat_id,item.message_id);

	        		                  if (playSound == false && data.uw == 'false' && (typeof item.ignore === 'undefined' || typeof item.ignore === false))
                                      {
                                          playSound = true;
                                      }

                                      if (data.uw == 'false') {
                                          ee.emitEvent('angularActionHappened',[{'type':'user_wrote','chat_id': item.chat_id, 'msg' : item.msg, 'nick': item.nck}]);
                                      }

	        		                  if ( confLH.new_message_browser_notification == 1 && data.uw == 'false' && (typeof item.ignore === 'undefined' || typeof item.ignore === false)) {
	        		                	  lhinst.showNewMessageNotification(item.chat_id,item.msg,item.nck);
	  	                			  };

	  	                			  if (item.msfrom > 0) {
	  	                				if ($('#msg-'+item.msfrom).attr('data-op-id') != item.msop) {
	  	                					$('#msg-'+item.msfrom).next().addClass('operator-changes');
	  	                				}
	  	                			  }

	  	                			  ee.emitEvent('eventSyncAdmin', [item,i]);
	                            });

	                            if ( confLH.new_message_sound_admin_enabled == 1  && data.uw == 'false' && playSound == true) {
	                            	lhinst.playNewMessageSound();
	                            };

	        	            };

	        	            if (data.result_status != 'false')
	        	            {
	        	            	var groupTabs = $('#group-chats-status').hasClass('chat-active');

	        	                $.each(data.result_status,function(i,item) {

	        	                      var typingIndicator = $('#user-is-typing-'+item.chat_id);

	        	                      if (item.tp == 'true') {
                                          if (lhinst.nodeJsMode == false) {
                                                typingIndicator.html(item.tx);
                                          }
	        	                          if (typingIndicator.css('visibility') == 'hidden') {
                                              typingIndicator.css('visibility','visible');
                                          }
	        	                      } else {
                                          if (lhinst.nodeJsMode == false) {
                                              typingIndicator.css('visibility','hidden');
                                          }
	        	                      };

                                      $('#last-msg-chat-'+item.chat_id).text(item.lmsg);

	        	                      var userChatStatus = $('#user-chat-status-'+item.chat_id);

	        	                      var wasOnline = userChatStatus.hasClass('icon-user-online');

	        	                      $('#chat-duration-'+item.chat_id).text(item.cdur);

									  userChatStatus.removeClass('icon-user-online icon-user-away icon-user-pageview');
	        	                      $('#msg-send-status-'+item.chat_id).removeClass('icon-user-online icon-user-offline');

	        	                      if (item.us == 0) {
                                          userChatStatus.addClass('icon-user-online');
	        	                      } else if (item.us == 2) {
                                          userChatStatus.addClass('icon-user-away');
	        	                      } else if (item.us == 3) {
                                          userChatStatus.addClass('icon-user-pageview');
	        	                      }

                                    if (groupTabs == true) {
                                        if (wasOnline == true && item.us != 0 || (lhinst.hidenicknamesstatus != groupTabs && item.us != 0)) {
                                            $('#ntab-chat-' + item.chat_id).hide();
                                        } else if (wasOnline == false && item.us == 0 || (lhinst.hidenicknamesstatus != groupTabs && item.us == 0)) {
                                            $('#ntab-chat-' + item.chat_id).show();
                                        }
                                    } else if (lhinst.hidenicknamesstatus != groupTabs) {
                                        $('#ntab-chat-' + item.chat_id).show();
									}

	        	                      var statusel = $('#chat-id-'+item.chat_id +'-mds');

	        	                      if (statusel.attr('data-chat-status') != item.cs || statusel.attr('data-chat-user') != item.co)
                                      {
                                          lhinst.updateVoteStatus(item.chat_id);
                                      }

	        	                      if (item.um == 1) {
	        	                    	  statusel.addClass('chat-unread');
	        	                    	  $('#msg-send-status-'+item.chat_id).addClass('icon-user-offline');
                                          if (item.ssub == 3) {
                                              $('#messagesBlock-'+item.chat_id).find('.msg-del-st-0,.msg-del-st-1').removeClass('msg-del-st-0 msg-del-st-1').addClass('msg-del-st-2').text('done_all');
                                          }
                                      } else {
	  	                				  $('#msg-send-status-'+item.chat_id).addClass('icon-user-online');
	  	                				  statusel.removeClass('chat-unread');
                                          $('#messagesBlock-'+item.chat_id).find('.msg-del-st-0,.msg-del-st-1,.msg-del-st-2').remove();
	  	                			  }

	        	                      if (item.lp !== false) {
	        	                    	  statusel.attr('title',item.lp+' s.');
	        	                      } else {
	        	                    	  statusel.attr('title','');
	        	                      }
	        	                      if (typeof item.oad != 'undefined' && item.oad == 1) {
                                          $('#lhc_sync_operation').remove();
                                          var th = document.getElementsByTagName('head')[0];
                                          var s = document.createElement('script');
                                          s.setAttribute('id','lhc_sync_operation');
                                          s.setAttribute('type','text/javascript');
                                          s.setAttribute('src',WWW_DIR_JAVASCRIPT + 'chat/loadoperatorjs/(type)/chat/(id)/'+item.chat_id);
                                          th.appendChild(s);
	        	                      };
	                            });
	        	            };

	        	            if (data.cg) {
                                $.each(data.cg,function(i,item) {
                                    return lhinst.removeDialogTab(item,$('#tabs'),true);
                                });
                            }

                            lhinst.hidenicknamesstatus = groupTabs;

                            clearTimeout(lhinst.userTimeout);
	        	            lhinst.userTimeout = setTimeout(chatsyncadmin,confLH.chat_message_sinterval);

                            ee.emitEvent('chatAdminSync', [data]);

	        	        };
        	    	} catch (err) {
                        clearTimeout(lhinst.userTimeout);
        	    		lhinst.userTimeout = setTimeout(chatsyncadmin,confLH.chat_message_sinterval);
					};

        	        //Allow another request to send check for messages
        	        lhinst.setSynchronizationRequestSend(false);

        	        if (LHCCallbacks.syncadmincall) {
    	        		LHCCallbacks.syncadmincall(lhinst,data);
    	        	};


            	}).fail(function(){
                    clearTimeout(lhinst.userTimeout);
            		lhinst.userTimeout = setTimeout(chatsyncadmin,confLH.chat_message_sinterval);
            		lhinst.setSynchronizationRequestSend(false);
            	});
	        } else {
                clearTimeout(lhinst.userTimeout);
	        	lhinst.userTimeout = setTimeout(chatsyncadmin,confLH.chat_message_sinterval);
	        }

	    } else {
	        this.isSinchronizing = false;
	    }
	};

	this.updateVoteStatus = function(chat_id, internal) {

        var that = this;

		$.getJSON(this.wwwDir + 'chat/updatechatstatus/'+chat_id ,{ }, function(data){
			$('#main-user-info-tab-'+chat_id).html(data.result);

            $('#messagesBlock-'+chat_id+' span.vis-tit').each(function(i) {
                var cache = $(this).children();
                $(this).text(' '+data.nick).prepend(cache);
            });

            $('#ntab-chat-'+chat_id).text(data.nick);

            ee.emitEvent('chatTabInfoReload', [chat_id]);

            !internal && that.channel && that.channel.postMessage({'action':'update_chat','args':{'chat_id' : parseInt(chat_id)}});
		});
	};

	this.updateChatLastMessageID = function(chat_id,message_id)
	{
	    this.chatsSynchronisingMsg[this.getChatIndex(chat_id)] = chat_id+','+message_id;
	};

	this.requestNotificationPermission = function() {
		if (window.webkitNotifications) {
			window.webkitNotifications.requestPermission();
		} else if(window.Notification){
			Notification.requestPermission(function(permission){});
		} else {
			alert('Notification API in your browser is not supported.');
		}
	};

	this.playNewChatAudio = function(sound) {
		clearTimeout(this.soundIsPlaying);
		this.soundPlayedTimes++;
		if (Modernizr.audio && this.audio !== null) {

			this.audio.src = Modernizr.audio.ogg ? WWW_DIR_JAVASCRIPT_FILES + '/'+sound+'.ogg?v=4' :
                        Modernizr.audio.mp3 ? WWW_DIR_JAVASCRIPT_FILES + '/'+sound+'.mp3?v=4' : WWW_DIR_JAVASCRIPT_FILES + '/'+sound+'.wav?v=4';
			this.audio.load();

            if (confLH.repeat_sound > this.soundPlayedTimes) {
            	var inst = this;
            	this.soundIsPlaying = setTimeout(function(){inst.playNewChatAudio(sound);},confLH.repeat_sound_delay*1000);
            }
	    };
	};

	this.focusChanged = function(status){
		if (confLH.new_message_browser_notification == 1 && status == true){
			if (window.webkitNotifications || window.Notification) {
				var inst = this;
				$.each(this.chatsSynchronising, function( index, chat_id ) {
					if (typeof inst.notificationsArrayMessages[chat_id] !== 'undefined') {
						if (window.webkitNotifications) {
							inst.notificationsArrayMessages[chat_id].cancel();
						} else {
							inst.notificationsArrayMessages[chat_id].close();
						}

						delete inst.notificationsArrayMessages[chat_id];
					}
				});
			}
		}

		// If it's customer chat make sure sync is running.
		if (parseInt(this.chat_id) > 0) {
            this.scheduleSync();
        }
	};

	this.notificationsArrayMessages = [];

	this.showNewMessageNotification = function(chat_id,message,nick) {
		try {

		if (window.Notification && focused == false && window.Notification.permission == 'granted') {
				if (typeof this.notificationsArrayMessages[chat_id] !== 'undefined') {
					this.notificationsArrayMessages[chat_id].close();
					delete this.notificationsArrayMessages[chat_id];
				};

  				var notification = new Notification(nick, { icon: WWW_DIR_JAVASCRIPT_FILES_NOTIFICATION + '/notification.png', body: message });
  				var _that = this;

  				notification.onclick = function () {
  					window.focus();
	    	        notification.close();
	    	        delete _that.notificationsArrayMessages[chat_id];
	    	    };

	    	    notification.onclose = function() {
	    	    	if (typeof _that.notificationsArrayMessages[chat_id] !== 'undefined') {
	    				delete _that.notificationsArrayMessages[chat_id];
	    			};
	    	    };

	    	    this.notificationsArrayMessages[chat_id] = notification;
	    	    this.scheduleNewMessageClose(notification,chat_id);
		  }
		} catch(err) {
        	console.log(err);
        };
	};

	this.scheduleNewMessageClose = function(notification, chat_id) {
		var _that = this;
		setTimeout(function() {
			if (window.webkitNotifications) {
				notification.cancel();
			} else {
				notification.close();
			};

			if (typeof _that.notificationsArrayMessages[chat_id] !== 'undefined') {
				delete _that.notificationsArrayMessages[chat_id];
			};

		},10*1000);
	};

	this.playSoundNewAction = function(identifier,chat_id,nick,message,nt) {

		if (this.backgroundChats.indexOf(parseInt(chat_id)) != -1) {
			return ;
		}

        ee.emitEvent('angularActionHappened',[{'type': identifier, 'chat_id': chat_id, 'msg' : message, 'nick': nick}]);

		if (confLH.new_chat_sound_enabled == 1 && (confLH.sn_off == 1 || $('#online-offline-user').text() == 'flash_on') && (identifier == 'active_chats' || identifier == 'bot_chats' || identifier == 'pmails' || identifier == 'amails' || identifier == 'transferred_mail' || identifier == 'pending_chat' || identifier == 'transfer_chat' || identifier == 'unread_chat' || identifier == 'pending_transfered')) {
	    	this.soundPlayedTimes = 0;
	        this.playNewChatAudio(identifier == 'active_chats' ? 'alert' : 'new_chat');
	    };

	    if(!$("textarea[name=ChatMessage]").is(":focus") && (confLH.sn_off == 1 || $('#online-offline-user').text() == 'flash_on') && (identifier == 'subject_chats' || identifier == 'active_chats' || identifier == 'bot_chats' || identifier == 'pending_chat' || identifier == 'transfer_chat' || identifier == 'unread_chat' || identifier == 'pending_transfered')) {
	    	this.startBlinking();
    	};

        if (identifier == 'subject_chats') {
            this.soundPlayedTimes = 0;
            this.playNewChatAudio('subject_chat');
        }

	    var inst = this;

	    if ( (identifier == 'subject_chats' || identifier == 'active_chats' || identifier == 'pmails' || identifier == 'amails' || identifier == 'transferred_mail' || identifier == 'pending_chat' || identifier == 'transfer_chat' || identifier == 'unread_chat' || identifier == 'bot_chats' || identifier == 'pending_transfered') && (confLH.sn_off == 1 || $('#online-offline-user').text() == 'flash_on') && window.Notification && window.Notification.permission == 'granted') {
			var notification = new Notification(nick, { icon: WWW_DIR_JAVASCRIPT_FILES_NOTIFICATION + '/notification.png', body: message, requireInteraction : true });

			notification.onclick = function () {

    	    	if (identifier == 'subject_chats' || identifier == 'active_chats' || identifier == 'pending_chat' || identifier == 'unread_chat' || identifier == 'pending_transfered' || identifier == 'bot_chats') {
    	    		if ($('#tabs').length > 0) {
    	    			window.focus();
                        inst.addOpenTrace('click_notification');
    	    			inst.startChat(chat_id, $('#tabs'), nt);
    	    		} else {
    	    			inst.startChatNewWindow(chat_id,'ChatRequest');
    	    		}
    	    	} else if (identifier == 'pmails' || identifier == 'amails') {
                    if ($('#tabs').length > 0) {
                        window.focus();
                        inst.startMailChat(chat_id, $('#tabs'), nt);
                    } else {
                        inst.startMailNewWindow(chat_id,'ChatMail');
                    }
                } else if (identifier == 'transferred_mail') {
                    inst.startChatNewWindowTransferByTransfer(chat_id, nt, 1);
                 } else {
    	    		inst.startChatNewWindowTransferByTransfer(chat_id, nt, 0);
    	    	};
    	        notification.close();
    	    };

    	    if (identifier != 'pending_transfered') {
    	        if (identifier == 'pmails' || identifier == 'amails' || identifier == 'transferred_mail')
                {
                    if (this.notificationsArrayMail[chat_id] !== 'undefined') {
                        notification.close();
                    }

                    this.notificationsArrayMail[chat_id] = notification;

                } else {
                    if (this.notificationsArray[chat_id] !== 'undefined') {
                        notification.close();
                    }

                    this.notificationsArray[chat_id] = notification;
                }
			};
	    };

	    if (identifier == 'transfer_chat' && confLH.accept_chats) {
            inst.startChatNewWindowTransferByTransfer(chat_id, nt, 0, true);
        } else if (identifier == 'transfer_chat' && confLH.show_alert_transfer == 1) {
            if (confirm(confLH.transLation.transfered + "\n\n" + message)) {
                inst.startChatNewWindowTransferByTransfer(chat_id, nt, 0);
			}
        }

        if (identifier == 'transferred_mail' && confLH.show_alert_transfer == 1) {
            if (confirm(confLH.transLation.transfered + "\n\n" + message)) {
                inst.startChatNewWindowTransferByTransfer(chat_id, nt, 1);
			}
        }

	    if (confLH.show_alert == 1) {
    		if (confirm(confLH.transLation.new_chat+"\n\n"+message)) {
    			if (identifier == 'pending_chat' || identifier == 'unread_chat' || identifier == 'pending_transfered' || identifier == 'bot_chats') {
    	    		if ($('#tabs').length > 0) {
    	    			window.focus();
                        inst.addOpenTrace('alert_open');
    	    			inst.startChat(chat_id, $('#tabs'), nt);
    	    		} else {
    	    			inst.startChatNewWindow(chat_id,'ChatRequest');
    	    		}
                } else if (identifier == 'pmails' || identifier == 'amails') {
                    if ($('#tabs').length > 0) {
                        window.focus();
                        inst.startMailChat(chat_id, $('#tabs'), nt);
                    } else {
                        inst.startMailNewWindow(chat_id,'ChatMail');
                    }
    	    	} else if (identifier == 'transferred_mail') {
                    inst.startChatNewWindowTransferByTransfer(chat_id, nt, 1);
    	    	} else {
    	    		inst.startChatNewWindowTransferByTransfer(chat_id, nt, 0);
    	    	};
    		};
	    };
	};

	this.syncadmininterfacestatic = function()
	{
		try {
            ee.emitEvent('angularLoadChatList');
		} catch(err) {
        	//
        };
	};

	this.addingUserMessage = false;
	this.addUserMessageQueue = [];
	this.addDelayedTimeout = null;

	this.addmsgadmin = function (chat_id, message)
	{
        $('#unread-separator-'+chat_id).remove();

		var textArea = $("#CSChatMessage-"+chat_id);

		if (textArea.is("[readonly]")) {
			return;
		}

		var pdata = {
				msg	: message || (textArea.prop('nodeName') == 'LHC-EDITOR' ? textArea[0].getContent() : textArea.val())
		};

		if (textArea.attr('meta-msg')) {
            pdata.meta_msg = textArea.attr('meta-msg');
            textArea.removeAttr('meta-msg');
        }

        if (textArea.attr('mode-write')) {
            pdata.mode_write = textArea.attr('mode-write');
        }

		if (pdata.msg == '') {
		    return;
        }

		if (this.speechHandler !== false) {
			this.speechHandler.messageSend();
		};

        message || (textArea.prop('nodeName') == 'LHC-EDITOR' ? textArea[0].setContent('',{'ignore_meta':true}) : textArea.val(''));

		var placeholerOriginal = textArea.attr('placeholder');

        textArea.attr('placeholder',confLH.transLation.sending || 'Sending...');

		if (textArea.hasClass('edit-mode')) {

			pdata.msgid = textArea.attr('data-msgid');

			$.postJSON(this.wwwDir + 'chat/updatemsg/' + chat_id, pdata , function(data){

			    textArea.attr('placeholder',placeholerOriginal);
                textArea.removeClass('edit-mode');
                textArea.removeAttr('data-msgid');

				if (data.error == 'f') {

					$('#msg-'+pdata.msgid).replaceWith(data.msg);

					if (LHCCallbacks.addmsgadmin) {
		        		LHCCallbacks.addmsgadmin(chat_id);
		        	};

		        	ee.emitEvent('chatAddMsgAdmin', [chat_id]);

                    lhinst.addQuateHandler(chat_id);

					return true;
				} else {
                    alert(data.result);
                }
			});

		} else {

			var inst = this;

			var messagesBlock = $('#messagesBlock-'+chat_id);

            message || messagesBlock.append("<div class=\"message-row message-admin pending-storage\"><div class=\"msg-body\"><span class=\"material-icons lhc-spin\">autorenew</span>" + $("<div>").text(pdata.msg).html() + "</div></div>");

			messagesBlock.scrollTop(messagesBlock.prop('scrollHeight'));

			if (this.addingUserMessage == false)
			{
				this.addingUserMessage = true;


				var hasSubjects = false;
                if (textArea.attr('subjects_ids')) {
                    pdata.subjects_ids = textArea.attr('subjects_ids');
                    textArea.removeAttr('subjects_ids');
                    hasSubjects = true;
                }

                if (textArea.attr('canned_id')) {
                    pdata.canned_id = textArea.attr('canned_id');
                    textArea.removeAttr('canned_id');
                }

                if (textArea.attr('whisper')) {
                    pdata.whisper = 1;
                }

				$.postJSON(this.wwwDir + this.addmsgurl + chat_id, pdata , function(data) {
                    textArea.removeAttr('readonly').attr('placeholder',placeholerOriginal);

                    if (data.error == 'false') {
                        if (LHCCallbacks.addmsgadmin) {
                            LHCCallbacks.addmsgadmin(chat_id);
                        };

                        ee.emitEvent('chatAddMsgAdmin', [chat_id]);

                        if (data.r != '') {
                            $('#messagesBlock-'+chat_id).append(data.r).scrollTop($("#messagesBlock-"+chat_id).prop("scrollHeight")).find('.pending-storage').remove();
                        };

                        if (data.hold_removed === true) {
                            $('#hold-action-'+chat_id).removeClass('btn-outline-info');
                        } else if (data.hold_added === true) {
                            $('#hold-action-'+chat_id).addClass('btn-outline-info');
                        }

                        if (hasSubjects == true || data.update_status === true) {
                            inst.updateVoteStatus(chat_id);
                        }

                        lhinst.syncadmincall();
                    } else {
                        if (typeof data.token !== 'undefined') {
                            confLH.csrf_token = data.token;
                        }

                        textArea.attr('placeholder',placeholerOriginal);

                        if (textArea.prop('nodeName') == 'LHC-EDITOR') {
                            textArea[0].insertConent(pdata.msg,{"convert_bbcode" : true});
                        } else {
                            textArea.val((textArea.val() + ' ' + pdata.msg).trim());
                        }


                        $('.pending-storage').first().remove();
                        var escaped = '<div style="margin:10px 10px 30px 10px;" class="alert alert-warning" role="alert">' + $("<div>").text(data.r).html() + '</div>';
                        $('#messagesBlock-'+chat_id).append(escaped).scrollTop($("#messagesBlock-"+chat_id).prop("scrollHeight"));
                    }

					inst.addingUserMessage = false;

                    if (inst.addUserMessageQueue.length > 0) {
                        var elementAdd = inst.addUserMessageQueue.shift()
                        inst.addmsgadmin(elementAdd.chat_id,elementAdd.msg);
                    }

					return true;
				}).fail(function(respose) {
                    textArea.attr('placeholder',placeholerOriginal);

                    if (textArea.prop('nodeName') == 'LHC-EDITOR') {
                        textArea[0].insertConent(pdata.msg,{"convert_bbcode" : true});
                    } else {
                        textArea.val(textArea.val() + ' ' + pdata.msg);
                    }

                    var escaped = '<div style="margin:10px 10px 30px 10px;" class="alert alert-warning" role="alert">' + $("<div>").text('You have weak internet connection or the server has problems. Try to refresh the page or send the message again.' + (typeof respose.status !== 'undefined' ? ' Error code ['+respose.status+']' : '') + (typeof respose.responseText !== 'undefined' ? respose.responseText : '')).html() + '</div>';
                    $('#messagesBlock-'+chat_id).append(escaped).scrollTop($("#messagesBlock-"+chat_id).prop("scrollHeight"));
                    $('.pending-storage').first().remove();
                    inst.addingUserMessage = false;
                    if (inst.addUserMessageQueue.length > 0) {
                        var elementAdd = inst.addUserMessageQueue.shift()
                        inst.addmsgadmin(elementAdd.chat_id,elementAdd.msg);
                    }
		    	});

			} else {
                textArea.attr('placeholder', placeholerOriginal);
                this.addUserMessageQueue.push({'chat_id':chat_id,'msg':pdata.msg});
			}
		}
	};

	this.editPrevious = function(chat_id) {
		var textArea = $('#CSChatMessage-'+chat_id);
        let presentValue = "";

        if (textArea.prop('nodeName') == 'LHC-EDITOR') {
            presentValue = textArea[0].getContent();
        } else {
            presentValue = textArea.val();
        }

		if (presentValue == '' && textArea.attr('disable-edit') !== "true") {
			$.getJSON(this.wwwDir + 'chat/editprevious/'+chat_id, function(data){
				if (data.error == 'f') {

                    if (textArea.prop('nodeName') == 'LHC-EDITOR') {
                        textArea[0].setContent(data.msg,{"convert_bbcode" : true});
                    } else {
                        textArea.val(data.msg);
                    }

					textArea.attr('data-msgid',data.id);
					textArea.addClass('edit-mode');
					$('#msg-'+data.id).addClass('edit-mode');
					if (LHCCallbacks.editPrevious) {
						LHCCallbacks.editPrevious(chat_id, data);
					}
				}
			});
		}
	};

	this.afterAdminChatInit = function (chat_id) {
		if (LHCCallbacks.afterAdminChatInit) {
			LHCCallbacks.afterAdminChatInit(chat_id);
		}
	};

    this.getInputSelection = function(elem) {
        if (typeof elem != "undefined") {
            s = elem[0].selectionStart;
            e = elem[0].selectionEnd;
            return elem.val().substring(s, e);
        } else {
            return '';
        }
    }

    this.handleBBCode = function(inst) {

        var elem = $(inst.attr('data-selector'));

        var bbcodeend = typeof inst.attr("data-bbcode-end") !== 'undefined' ?  inst.attr("data-bbcode-end") : inst.attr("data-bbcode");

        if (elem.prop('nodeName') == 'LHC-EDITOR') {
            elem[0].insertFormating(inst.attr("data-bbcode"),bbcodeend);
        } else {
            var str = elem.val();
            if (typeof elem != "undefined") {
                var s = elem[0].selectionStart, e = elem[0].selectionEnd;
                var selection = str.substring(s, e);
            } else {
                var selection = '';
            }

            if (selection.length > 0) {
                $(inst.attr('data-selector')).val(str.substr(0,s) + "[" + inst.attr("data-bbcode") + "]" + selection + "[/" + bbcodeend + "]" + str.substring(e));
            } else {
                $(inst.attr('data-selector')).val(str + "[" + inst.attr("data-bbcode") + "]" + "[/" + bbcodeend + "]");
            }
        }

        return false;

    }

	this.addAdminChatFinished = function(chat_id, last_message_id, arg) {

		var _that = this;

		var $textarea = jQuery('#CSChatMessage-'+chat_id);

        if ($textarea.prop('nodeName') != 'LHC-EDITOR') {
            var cannedMessageSuggest = new LHCCannedMessageAutoSuggest({
                'chat_id': chat_id,
                'uppercase_enabled': confLH.auto_uppercase
            });
        }

		var colorPickerDom = document.getElementById('color-picker-chat-' + chat_id);

		if (colorPickerDom !== null) {
            var colorP = new ColorPicker({
                dom: document.getElementById('color-picker-chat-' + chat_id),
                value: '#0F0'
            });

            colorP.addEventListener('change', function (colorItem) {
                $('#color-apply-'+chat_id).attr('data-bbcode','color='+colorP.getValue('hex'));
            });

            $('.downdown-menu-color-'+chat_id).on('click', function (e) {
                if ($(this).parent().is(".show")) {
                    var target = $(e.target);
                    if (target.hasClass("keepopen") || target.parents(".keepopen").length){
                        return false;
                    } else {
                        return true;
                    }
                }
            });

            $('.downdown-menu-color-'+chat_id+' .color-item').on('click',function () {
                colorP.setValue($(this).attr('data-color'));
            });
        }

        $textarea.bind('click', function (evt) {
            $('.dropdown-menu-main').removeClass('show').find('> .dropdown-menu').removeClass('show');

            if ($textarea.prop('nodeName') != 'LHC-EDITOR') {
                $('#CSChatMessage-'+chat_id).focus();
            }

            if (!$('#chat-tab-link-'+chat_id).hasClass('active')) {
                $('#chat-tab-link-'+chat_id).click();
                (new bootstrap.Tab(document.querySelector('#chat-tab-link-'+chat_id))).show();
            }
        });

        $('#dropdown-menu-main-action-'+chat_id).click(function(){
            $(this).parent().toggleClass('show');
            $(this).parent().find('> div.dropdown-menu').toggleClass('show');
        });

        if ($textarea.prop('nodeName') != 'LHC-EDITOR') {
            $textarea.bind('keydown', 'return', function (evt) {
                _that.addmsgadmin(chat_id);
                ee.emitEvent('afterAdminMessageSent',[chat_id]);
                $textarea[0].rows = $textarea.attr('data-rows-default');
                return false;
            });

            $textarea.bind('keyup', 'up', function (evt){
                _that.editPrevious(chat_id);
            });

            $textarea.bind('keyup', function (evt){

                if ($textarea.val() == '') {
                    $textarea.removeAttr('subjects_ids');
                    $textarea.removeAttr('canned_id');
                    $textarea.removeAttr('content_modified');
                }

                if ($textarea.val() == '' && evt.altKey && (evt.which == 38 || evt.which == 40)) {
                    if (confLH.new_dashboard == true) {
                        ee.emitEvent('activateNextTab',[chat_id,(evt.which == 38 ? true : false)]);
                    } else {

                        if (evt.which == 38) {
                            var tab = lhinst.smartTabFocus($('#tabs'),chat_id,{keep:true,up:true});
                        } else {
                            var tab = lhinst.smartTabFocus($('#tabs'),chat_id,{keep:true,up:false});
                        }

                        var parts = tab.split('chat-id-');

                        if (parts[1] && !isNaN(parts[1].replace('mc',''))) {
                            $('#tabs > div > div.chat-tab-pane.active.show:not(#chat-id-' + parts[1] + ')').removeClass('active show');
                            $('#chat-tab-link-'+parts[1]).click();
                        }
                    }
                    return ;
                }

                var ta = $textarea[0];

                if ((evt.which == 38 || evt.which == 8 || evt.which == 46) && ta.value.split(/\r\n|\r|\n/).length <= ta.rows && parseInt($textarea.attr('data-rows-default')) <= ta.value.split(/\r\n|\r|\n/).length) {
                    ta.rows = ta.value.split(/\r\n|\r|\n/).length;
                }

                var maxrows = 30;
                var lh = ta.clientHeight / ta.rows;
                while (ta.scrollHeight > ta.clientHeight && !window.opera && ta.rows < maxrows) {
                    ta.style.overflow = 'hidden';
                    ta.rows += 1;
                }
                if (ta.scrollHeight > ta.clientHeight) ta.style.overflow = 'auto';

                if ($textarea.val() != '') {
                    $textarea.attr('content_modified',true);
                }

            });
        }

		// Resize by user
		$messageBlock = $('#messagesBlock-'+chat_id);

		$messageBlock.css('height',this.getLocalValue('lhc_mheight',confLH.defaultm_hegiht));

		$messageBlock.data('resized',false);
		$messageBlock.data('y', $messageBlock.outerHeight());

		$messageBlock.bind('mouseup mousemove',function(event) {
			  var $this = jQuery(this);

		      if ($this.outerHeight() != $this.data('y')) {
		    	   if ($this.data('resized') == false) {
		    		   $this.css('height','1px');
		    		   $this.data('resized',true)
		    	   }

		    	   if (this.resize_timeout) {
		    		   clearTimeout(this.resize_timeout);
		    	   }

		    	   this.resize_timeout = setTimeout(function(){
		    		   _that.setLocalValue('lhc_mheight', $this.outerHeight());
		    		   $this.data('y', $this.outerHeight());
		    	   },100);
		      }
		});

        if (confLH.scroll_load == 1) {
            $messageBlock[0].oldScrollTop = $messageBlock[0].scrollTop;
            $messageBlock.bind('scroll',function(event) {

                if (_that.scrollLoading == true) {
                    return ;
                }

                var $this = jQuery(this);

                if ($this[0].oldScrollTop > $this[0].scrollTop && $this[0].scrollTop < 300 && $('#load-prev-btn-'+chat_id).length == 1) {
                    _that.loadPreviousMessages($('#load-prev-btn-'+chat_id), true);
                }

                $this[0].oldScrollTop = $this[0].scrollTop;
            });
        }

		this.initTypingMonitoringAdmin(chat_id);

		this.afterAdminChatInit(chat_id);

		this.addSynchroChat(chat_id,last_message_id);

        confLH.no_scroll_bottom !== 1 && $messageBlock.prop('scrollTop',$messageBlock.prop('scrollHeight'));

		// Start synchronisation
		this.startSyncAdmin();

		// Hide notification only if chat was not started in background
		if (arg === null || typeof arg !== 'object' || arg.indexOf('background') === -1) {
			this.hideNotification(chat_id);
		} else {
			$('#chat-tab-link-'+chat_id).click(function() {
				_that.removeBackgroundChat(parseInt(chat_id));
				_that.hideNotification(parseInt(chat_id));
			});
		}

		try {
			if (localStorage) {
				if (localStorage.getItem('lhc_rch') == 1) {
					this.processCollapse(chat_id);
				}
			}
		} catch(e) {};

        $('#chat-tab-items-' + chat_id+' > li > a').click(function(){
            ee.emitEvent('adminChatTabSubtabClicked', [chat_id,$(this)]);
        });

        $('#chat-write-button-'+chat_id).click(function() {
            $('#CSChatMessage-'+chat_id).show().focus().removeAttr("whisper").removeClass('bg-light').attr('placeholder',$(this).attr('data-plc'));
            $(this).removeClass('btn-outline-secondary').addClass('btn-outline-primary');
            $('#chat-preview-button-'+chat_id+',#chat-whisper-button-'+chat_id).removeClass('btn-outline-primary').addClass('btn-outline-secondary');
            $('#chat-preview-container-'+chat_id).hide();
            $('#chat-join-as-container-'+chat_id).removeClass('hide');
        });

        $('#chat-preview-button-'+chat_id).click(function() {
            $('#chat-preview-container-'+chat_id).html('...').show();
            $('#CSChatMessage-'+chat_id).hide();
            $(this).removeClass('btn-outline-secondary').addClass('btn-outline-primary');
            $('#chat-join-as-container-'+chat_id).addClass('hide');
            $('#chat-write-button-'+chat_id+',#chat-whisper-button-'+chat_id).removeClass('btn-outline-primary').addClass('btn-outline-secondary');
            jQuery.post(WWW_DIR_JAVASCRIPT +'chat/previewmessage', {msg_body: true, 'msg' : _that.getMessageContent(chat_id)}, function(data){
                $('#chat-preview-container-'+chat_id).html(data);
            });
        });

        $('#chat-whisper-button-'+chat_id).click(function() {
            $('#CSChatMessage-'+chat_id).show().focus().attr('whisper','1').addClass('bg-light').attr('placeholder',$(this).attr('data-plc'));
            $('#chat-preview-container-'+chat_id).hide();
            $(this).removeClass('btn-outline-secondary').addClass('btn-outline-primary');
            $('#chat-write-button-'+chat_id+',#chat-preview-button-'+chat_id).removeClass('btn-outline-primary').addClass('btn-outline-secondary');
            $('#chat-join-as-container-'+chat_id).addClass('hide');
        });

        $('#chat-join-as-'+chat_id).click(function(){
            $('#chat-join-as-container-'+chat_id).addClass('hide mode-chosen');
            $('chat-write-button-'+chat_id).attr('data-plc',$('#chat-mode-selected-'+chat_id).find(":selected").attr('data-plc'));
            $('#CSChatMessage-'+chat_id).attr('placeholder',$('#chat-mode-selected-'+chat_id).find(":selected").attr('data-plc'));
            $('#CSChatMessage-'+chat_id).attr('mode-write',$('#chat-mode-selected-'+chat_id).val()).focus();
        });

        $('#chat-impersonate-option-'+chat_id).click(function(){
            $('#chat-write-button-'+chat_id).click();
            $('#chat-join-as-container-'+chat_id).removeClass('hide mode-chosen');
        });

		ee.emitEvent('adminChatLoaded', [chat_id,last_message_id,arg]);
	};

    this.getMessageContent = function(chat_id){
        let elm = document.getElementById('CSChatMessage-'+chat_id);
        if (elm) {
            if (elm.nodeName == 'LHC-EDITOR') {
                return elm.getContent();
            } else {
                return elm.value;
            }
        }
    }

    this.setFocus = function(chat_id) {
        let elm = document.getElementById('CSChatMessage-'+chat_id);
        if (elm) {
            if (elm.nodeName == 'LHC-EDITOR') {
                return elm.setFocus && elm.setFocus();
            } else {
                return elm.focus();
            }
        }
    }

	this.removeBackgroundChat = function(chat_id) {
		var index = this.backgroundChats.indexOf(parseInt(chat_id));
		if (index !== -1) {
			delete this.backgroundChats[index];
		};
	};

	this.getLocalValue = function(variable,defaultValue) {
		try {
			if (localStorage) {
				var value = localStorage.getItem(variable);
				if (value !== null) {
						return value;
				} else {
					return defaultValue;
				}
			}
		} catch(e) {}
		return defaultValue;
	};

	this.setLocalValue = function(key,val){
		try {
	    	if (localStorage) {
				localStorage.setItem(key,val);
			}
    	} catch(e) {}
	};

	this.hideNotification = function(chat_id, type)
	{
        chat_id = parseInt(chat_id);

	    if (typeof type === 'undefined' || type != 'mail') {
            if (typeof this.notificationsArray[chat_id] !== 'undefined' && this.backgroundChats.indexOf(chat_id) == -1) {
                this.notificationsArray[chat_id].close();
                delete this.notificationsArray[chat_id];
            };
        } else {
            if (typeof this.notificationsArrayMail[chat_id] !== 'undefined') {
                this.notificationsArrayMail[chat_id].close();
                delete this.notificationsArrayMail[chat_id];
            };
        }
		clearTimeout(this.soundIsPlaying);
	}

	this.showMyPermissions = function(user_id) {
		$.get(this.wwwDir + 'permission/getpermissionsummary/'+user_id, function(data){
			$('#permissions-summary').html(data);
		});
	};

    this.updateMessageRowAdmin = function(chat_id, msgid){
    	$.getJSON(this.wwwDir + 'chat/getmessageadmin/' + chat_id + '/' + msgid, function(data) {
    		if (data.error == 'f') {

                var messagesBlock = $('#messagesBlock-' + chat_id);
                var needScroll = (messagesBlock.prop('scrollTop') + messagesBlock.height() + 30) > messagesBlock.prop('scrollHeight');

    			$('#msg-'+msgid).replaceWith(data.msg);
                lhinst.addQuateHandler(chat_id);
    			$('#msg-'+msgid).addClass('msg-updated');
    			setTimeout(function(){
    				$('#msg-'+msgid).removeClass('msg-updated');
    			},2000);
                needScroll && messagesBlock.scrollTop(messagesBlock.prop('scrollHeight'));
    		}
		});
    };

    this.startSyncAdmin = function()
    {
        if (this.isSinchronizing == false)
        {
            this.isSinchronizing = true;
            this.syncadmincall();
        }
    };

    this.disableChatSoundAdmin = function(inst)
    {
        if (inst.prop('tagName') != 'I') {
            inst = inst.find('> i.material-icons');
        }

    	if (inst.text() == 'volume_off'){
    		$.post(this.wwwDir + 'user/setsettingajax/chat_message/1');
    		confLH.new_message_sound_admin_enabled = 1;
    		inst.text('volume_up');
    	} else {
    		$.post(this.wwwDir + 'user/setsettingajax/chat_message/0');
    		confLH.new_message_sound_admin_enabled = 0;
    		inst.text('volume_off');
    	}
    	return false;
    };

    this.disableNewChatSoundAdmin = function(inst)
    {
        if (inst.prop('tagName') != 'I') {
            inst = inst.find('> i.material-icons');
        }

    	if (inst.text() == 'volume_off'){
    		$.post(this.wwwDir+  'user/setsettingajax/new_chat_sound/1');
    		confLH.new_chat_sound_enabled = 1;
    		inst.text('volume_up');
    	} else {
    		$.post(this.wwwDir+  'user/setsettingajax/new_chat_sound/0');
    		confLH.new_chat_sound_enabled = 0;
    		inst.text('volume_off');
    	}
    	return false;
    };

    this.changeUserSettings = function(attr,value){
    	$.post(this.wwwDir+  'user/setsettingajax/'+attr+'/'+value);
    };

    this.changeUserSettingsIndifferent = function(attr,value) {
    	$.post(this.wwwDir+  'user/setsettingajax/'+attr+'/'+encodeURIComponent(value == '' ? '__empty__' : value)+'/(indifferent)/true');
    };

    this.changeStatusAction = function(form,chat_id){
    	var inst = this;
    	$.postJSON(form.attr('action'),form.serialize(), function(data) {
	   		 if (data.error == 'false') {
	   			$('#myModal').modal('hide');
	   			inst.updateVoteStatus(chat_id);
	   			if (data.is_owner === true) {
                    $('#CSChatMessage-'+chat_id).attr('placeholder','');
                    inst.setFocus(chat_id);
                }
	   		 } else {
	   			 alert(data.result);
	   		 }
	   	 });
    	return false;
    };

    this.submitModalForm = function(form, idElement){
        var inst = this;

        $('#modal-in-progress').removeClass('hide');
        $('.modal-submit-disable').addClass('disabled').attr('disabled',"disabled");

        $.ajax({
            url: form.attr("action"),
            type: form.attr("method"),
            //dataType: "JSON",
            data: new FormData(form[0]),
            processData: false,
            contentType: false,
            success: function (data, status)
            {
                $('#modal-in-progress').addClass('hide');
                $('.modal-submit-disable').removeClass('disabled').attr('disabled',"disabled");

                var idElementDetermined = idElement ? '#'+idElement : '#myModal';
                if (!idElement) {
                    var styleOriginal = $('#myModal > .modal-dialog')[0].style.cssText;
                }
                $(idElementDetermined).html(data);
                if (!idElement && $('#myModal > .modal-dialog').length > 0) {
                    $('#myModal > .modal-dialog')[0].style.cssText = styleOriginal;
                } else {
                    $(idElementDetermined).html('<div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-body">'+data+'</div></div></div>');
                }
            },
            error: function (xhr, desc, err)
            {
                alert('An error has accoured! ' + xhr.responseText);
            }
        });

        return false;
    };

    this.pendingMessagesToStore = [];

    this.setSubject = function(inst, chat_id) {
        $('#subject-message-'+chat_id).text('...');
        $.postJSON(this.wwwDir + 'chat/subject/'+chat_id + '/(subject)/' + inst.val() + '/(status)/' + inst.is(':checked'),{'update': true}, function(data) {
            lhinst.updateVoteStatus(chat_id);
            $('#subject-message-'+chat_id).text(data.message);
        });
    }

    this.deleteChatfile = function(file_id){
    	$.postJSON(this.wwwDir + 'file/deletechatfile/' + file_id, function(data){
    		if (data.error == 'false') {
    			$('#file-id-'+file_id).remove();
    		} else {
    			alert(data.result);
    		}
    	});
    };

    this.updateChatFiles = function(chat_id) {
    	$.postJSON(this.wwwDir + 'file/chatfileslist/' + chat_id, function(data){
    		$('#chat-files-list-'+chat_id).html(data.result);
    	});
    };

    this.updateOnlineFiles = function(online_user_id) {
    	$.postJSON(this.wwwDir + 'file/onlinefileslist/' + online_user_id, function(data){
    		$('#online-user-files-list-'+online_user_id).html(data.result);
    	});
    };

    this.updateOnlineFilesUser = function(online_user_vid) {
    	$.postJSON(this.wwwDir + 'file/useronlinefileslist/' + online_user_vid, function(data){
    		$('#user-online-files-list').html(data.result);
    	});
    };

    this.addFileUpload = function(data_config) {
    	$('#fileupload-'+data_config.chat_id).fileupload({
    		url: this.wwwDir + 'file/uploadfileadmin/'+data_config.chat_id,
    		dataType: 'json',
    		add: function(e, data) {
    			var uploadErrors = [];
    			var acceptFileTypes = data_config.ft_op;
    			if(!(acceptFileTypes.test(data.originalFiles[0]['type']) || acceptFileTypes.test(data.originalFiles[0]['name']))) {
    				uploadErrors.push(data_config.ft_msg);
    			};
    			if(data.originalFiles[0]['size'] > data_config.fs) {
    				uploadErrors.push(data_config.fs_msg);
    			};
    			if(uploadErrors.length > 0) {
    				alert(uploadErrors.join("\n"));
    			} else {
    				data.submit();
    			};
    		},
    		done: function(e,data) {
				var response = data.response();
				if (response != undefined && response.result != undefined && response.result.error == 'true' && response.result.error_msg != undefined) {
					alert(response.result.error_msg);
				} else {
					lhinst.updateChatFiles(data_config.chat_id);

                    var txtArea = $('#CSChatMessage-'+data_config.chat_id);
                    if (txtArea.prop('nodeName') == 'LHC-EDITOR') {
                        txtArea[0].insertContent(response.result.msg,{"new_line" : true,"convert_bbcode" : true});
                    } else {
                        var txtValue = jQuery.trim(txtArea.val());
                        txtArea.val(txtValue + (txtValue != '' ? "\n" : "") + response.result.msg + "\n");
                    }
				}

				if (LHCCallbacks.addFileUpload) {
    				LHCCallbacks.addFileUpload(data_config.chat_id);
    			}
    		},
    		dropZone: $('#CSChatMessage-'+data_config.chat_id),
    		pasteZone: $('#CSChatMessage-'+data_config.chat_id).prop('nodeName') == 'LHC-EDITOR' ? $('#CSChatMessage-'+data_config.chat_id+' > .form-send-textarea') : $('#CSChatMessage-'+data_config.chat_id),
    		progressall: function (e, data) {
    			var progress = parseInt(data.loaded / data.total * 100, 10);
    			$('#user-is-typing-'+data_config.chat_id).css('visibility','visible');
    			$('#user-is-typing-'+data_config.chat_id).html(progress+'%');
    		}}).prop('disabled', !$.support.fileInput)
    		.parent().addClass($.support.fileInput ? undefined : 'disabled');
    };

    this.addExecutionCommand = function(online_user_id,operation) {
    	$.postJSON(this.wwwDir + 'chat/addonlineoperation/' + online_user_id,{'operation':operation}, function(data){
    		if (LHCCallbacks.addExecutionCommand) {
   	        	LHCCallbacks.addExecutionCommand(online_user_id);
   	        };
    	});
    	if (operation == 'lhc_screenshot') {
    		$('#user-screenshot-container').html('').addClass('screenshot-pending');
    		var inst = this;
    		setTimeout(function(){
    			inst.updateScreenshotOnline(online_user_id);
    		},15000);
    	};
    };

    this.addRemoteCommand = function(chat_id,operation) {
    	$.postJSON(this.wwwDir + 'chat/addoperation/' + chat_id,{'operation':operation}, function(data){
    		if (LHCCallbacks.addRemoteCommand) {
    			LHCCallbacks.addRemoteCommand(chat_id);
    		};
			if (data.error == 'true' && data.errors != null) {
				alert(data.errors.join("\n"));
			}
    	});
    	if (operation == 'lhc_screenshot') {
    		$('#user-screenshot-container').html('').addClass('screenshot-pending');
    		var inst = this;
    		setTimeout(function(){
    			inst.updateScreenshot(chat_id);
    		},5000);
    	};
    };

    this.addRemoteOnlineCommand = function(online_user_id,operation) {
    	$.postJSON(this.wwwDir + 'chat/addonlineoperationiframe/' + online_user_id,{'operation':operation}, function(data){
    		if (LHCCallbacks.addRemoteOnlineCommand) {
   	        	LHCCallbacks.addRemoteOnlineCommand(online_user_id);
   	        };
    	});
    };

    this.updateScreenshot = function(chat_id) {
    	$('#user-screenshot-container').html('').addClass('screenshot-pending');
    	$.get(this.wwwDir + 'chat/checkscreenshot/' + chat_id,function(data){
    		$('#user-screenshot-container-'+chat_id).html(data);
    		$('#user-screenshot-container-'+chat_id).removeClass('screenshot-pending');
    	});
    };

    this.updateScreenshotOnline = function(online_id) {
    	$('#user-screenshot-container').html('').addClass('screenshot-pending');
    	$.get(this.wwwDir + 'chat/checkscreenshotonline/' + online_id,function(data){
    		$('#user-screenshot-container-'+online_id).html(data);
    		$('#user-screenshot-container-'+online_id).removeClass('screenshot-pending');
    	});
    };

    this.delayQueue = [];
    this.delayed = false;
    this.intervalPending = null;

    this.gmaps_loading = false;
    this.queue_render = [];

    this.showMessageLocation = function(id,lat,lon) {
        var myLatLng = {lat: lat, lng: lon};

        if (this.gmaps_loaded == true) {

            var map = new google.maps.Map(document.getElementById('msg-location-' + id), {
                zoom: 13,
                center: myLatLng
            });

            var marker = new google.maps.Marker({
                position: myLatLng,
                map: map,
                title: lat+","+lon
            });

        } else {
            if (this.gmaps_loading == false) {
                this.gmaps_loading = true;
                var po = document.createElement('script'); po.type = 'text/javascript';
                po.async = true;
                po.src = 'https://maps.googleapis.com/maps/api/js?key='+confLH.gmaps_api_key+"&callback=chatMapLoaded";
                var s = document.getElementsByTagName('script')[0];
                s.parentNode.insertBefore(po, s);
                lhinst.queue_render.push({'id':id,'lat':lat,'lon':lon});
            } else {
                lhinst.queue_render.push({'id':id,'lat':lat,'lon':lon});
            }
        }
    }

    this.startChatNewWindow = function(chat_id,name)
    {
        var popupWindow = window.open(this.wwwDir + 'chat/single/'+chat_id,'chatwindow-chat-id-'+chat_id,"menubar=1,resizable=1,width=800,height=650");

        if (popupWindow !== null) {
            popupWindow.focus();
            var inst = this;
            setTimeout(function(){
                inst.syncadmininterfacestatic();
                history.pushState({}, '', '#chatlist');
                document.location.hash = '#chatlist';
            },1000);
            ee.emitEvent('chatStartOpenWindow', [chat_id]);
        }
    };
    
    this.setCloseWindowOnEvent = function (value)
    {
        this.closeWindowOnChatCloseDelete = value;
    };

    this.zoomImage = function(e) {
        if (!e.classList.contains('img-remote')) {
            lhc.revealModal({'url':e.src + '?modal=true'})
        } else {
            lhc.revealModal({'url': WWW_DIR_JAVASCRIPT + 'file/downloadfile/0/0' + '?modal=external&src='+e.src})
        }
    }
}

function chatMapLoaded()
{
    if (lhinst.queue_render.length > 0){
        lhinst.gmaps_loaded = true;
        var i = lhinst.queue_render.pop();

        var myLatLng = {lat: i.lat, lng: i.lon};

        var map = new google.maps.Map(document.getElementById('msg-location-' + i.id), {
            zoom: 13,
            center: myLatLng
        });

        var marker = new google.maps.Marker({
            position: myLatLng,
            map: map,
            title: i.lat+","+i.lon
        });

        if (lhinst.queue_render.length > 0) {
            chatMapLoaded();
        }
    }
}

var lhinst = new lh();
lhinst.playPreloadSound();

function preloadSound() {
	lhinst.playPreloadSound();
	jQuery(document).off("click", preloadSound);
	jQuery(document).off("touchstart", preloadSound);
}

jQuery(document).on("click", preloadSound);
jQuery(document).on("touchstart", preloadSound);

jQuery(document).on("click", function(){
    lhinst.hidePopover();
});

function gMapsCallback(){

    lhinst.gmaps_loaded = true;

	var $mapCanvas = $('#map_canvas');

	var map = new google.maps.Map($mapCanvas[0], {
        zoom: GeoLocationData.zoom,
        center: new google.maps.LatLng(GeoLocationData.lat, GeoLocationData.lng),
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        disableDefaultUI: true,
        options: {
            zoomControl: true,
            scrollwheel: true,
            streetViewControl: true
        }
    });

	var locationSet = false;
	
	var processing = false;
	var pendingProcess = false;
	var pendingProcessTimeout = false;
		

	google.maps.event.addListener(map, 'idle', showMarkers);
	
	var mapTabSection = $('#map-activator');
		
	function showMarkers() {
	    if ( processing == false) {	    		
	    	if (mapTabSection.hasClass('active')) {
		        processing = true;
	    		$.ajax({
	    			url : WWW_DIR_JAVASCRIPT + 'chat/jsononlineusers'+(parseInt($('#id_department_map_id').val()) > 0 ? '/(department)/'+parseInt($('#id_department_map_id').val()) : '' )+(parseInt($('#svelte-maxrowsFilter').val()) > 0 ? '/(maxrows)/'+parseInt($('#svelte-maxrowsFilter').val()) : '' )+(parseInt($('#svelte-userTimeoutFilter').val()) > 0 ? '/(timeout)/'+parseInt($('#svelte-userTimeoutFilter').val()) : '' ),
	    			dataType: "json",
	    			error:function(){
	    				clearTimeout(pendingProcessTimeout);
	    				pendingProcessTimeout = setTimeout(function(){
							showMarkers();
						},10000);
	    			},
	    			success : function(response) {
	    				bindMarkers(response);
	    				processing = false;
	    				clearTimeout(pendingProcessTimeout);
	    				if (pendingProcess == true) {
	    				    pendingProcess = false;
	    				    showMarkers();
	    				} else {
	    					pendingProcessTimeout = setTimeout(function(){
	    						showMarkers();
	    					},10000);
	    				}
	    			}
	    		});
    		} else {
    			pendingProcessTimeout = setTimeout(function(){
					showMarkers();
				},10000);
    		}    		
	    } else {
	       pendingProcess = true;
	    }
 	};

 	var markers = [];
 	var markersObjects = [];

 	var infoWindow = new google.maps.InfoWindow({ content: 'Loading...' });

 	function bindMarkers(mapData) {
		$(mapData.result).each(function(i, e) {

		    if ($.inArray(e.Id,markers) == -1) {
    			var latLng = new google.maps.LatLng(e.Latitude, e.Longitude);
    			var marker = new google.maps.Marker({ position: latLng, icon : e.icon, map : map });

    			google.maps.event.addListener(marker, 'click', function() {    			
    				lhc.revealModal({'url':WWW_DIR_JAVASCRIPT+'chat/getonlineuserinfo/'+e.Id})    				
    			});

    			marker.setVisible(true);
    			marker.setAnimation(google.maps.Animation.DROP);
    			markersObjects[e.Id] = marker;
    			markers.push(e.Id);
    			clearTimeout(markersObjects[e.Id].timeOutMarker);

    			markersObjects[e.Id].timeOutMarker = setTimeout(function(){
            		markers.splice($.inArray(e.Id,markers), 1);
            		google.maps.event.clearInstanceListeners(markersObjects[e.Id]);
            		markersObjects[e.Id].setMap(null);
            		markersObjects[e.Id] = null;
            	},parseInt($('#markerTimeout option:selected').val())*1000);

            } else {
            	markersObjects[e.Id].setIcon(e.icon);
            	clearTimeout(markersObjects[e.Id].timeOutMarker);
            	markersObjects[e.Id].timeOutMarker = setTimeout(function(){
            		markers.splice($.inArray(e.Id,markers), 1);
            		google.maps.event.clearInstanceListeners(markersObjects[e.Id]);
            		markersObjects[e.Id].setMap(null);
            		markersObjects[e.Id] = null;
            	},parseInt($('#markerTimeout option:selected').val())*1000);
            }
		});
	};
	
	$('#id_department_map_id').change(function(){
		showMarkers();
		lhinst.changeUserSettingsIndifferent('omap_depid',$(this).val());
	});
	
	$('#markerTimeout').change(function(){
		showMarkers();
		lhinst.changeUserSettingsIndifferent('omap_mtimeout',$(this).val());
	});
	
	$('#map-activator').click(function(){
		setTimeout(function(){
			google.maps.event.trigger(map, 'resize');
			if (locationSet == false) {
				locationSet = true;
				map.setCenter(new google.maps.LatLng(GeoLocationData.lat, GeoLocationData.lng));
			}
		},500);	
		showMarkers();
	});
};

var focused = true;
window.onfocus = window.onblur = function(e) {
    focused = (e || event).type === "focus";
    lhinst.focusChanged(focused);
};

window.lhcSelector = null;

$( document ).ready(function() {
    lhinst.protectCSFR();
})

/*Helper functions*/

function chatsyncadmin()
{
    lhinst.syncadmincall();
}

} catch (e) {
    if (lhcError) lhcError.log(e.message, "lh.js", e.lineNumber || e.line, e.stack); else throw Error("lhc : " + e.message);
}