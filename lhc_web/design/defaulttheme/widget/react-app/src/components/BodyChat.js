import React, { Component } from 'react';
import { connect } from "react-redux";

import { endChat } from "../actions/chatActions"
import { helperFunctions } from "../lib/helperFunctions";
import { Suspense, lazy } from 'react';

import { STATUS_CLOSED_CHAT, STATUS_BOT_CHAT, STATUS_SUB_SURVEY_SHOW, STATUS_SUB_USER_CLOSED_CHAT, STATUS_SUB_CONTACT_FORM } from "../constants/chat-status";

const OfflineChat = React.lazy(() => import('./OfflineChat'));
const ProactiveInvitation = React.lazy(() => import('./ProactiveInvitation'));
const CustomHTML = React.lazy(() => import('./CustomHTML'));

import HeaderChat from './HeaderChat';
import StartChat from './StartChat';
import OnlineChat from './OnlineChat';

@connect((store) => {
    return {
        chatwidget: store.chatwidget
    };
})

class BodyChat extends Component {

    state = {

    };

    constructor(props) {
        super(props);
        this.endChat = this.endChat.bind(this);
        this.popupChat = this.popupChat.bind(this);
        this.cancelClose = this.cancelClose.bind(this);
        this.setProfile = this.setProfile.bind(this);
        this.setMessages = this.setMessages.bind(this);
        this.setHideMessageField = this.setHideMessageField.bind(this);
        this.setBotPayload = this.setBotPayload.bind(this);
        this.switchColumn = this.switchColumn.bind(this);
        this.lastHeiht = 0;

        this.profileHTML = null;
        this.messagesHTML = null;
        this.hideMessageField = false;
        this.botPayload = null;

        this.textareaRef = React.createRef();

        helperFunctions.eventEmitter.addListener('end_chat_visitor', (e) => this.endChat());
    }

    cancelClose() {
        this.props.dispatch({'type' : 'UI_STATE', 'data' : {'attr': 'confirm_close', 'val': 0}})
    }

    setBotPayload(params) {
        this.botPayload = params;
    }

    endChat(params) {

        if (typeof params === 'undefined') {
            params = {};
        }

        // Contact form was filled from live chat
        if (this.props.chatwidget.get('isChatting') === true && this.props.chatwidget.get('isOnline') === true && this.props.chatwidget.get('isOfflineMode') === true && this.props.chatwidget.getIn(['chat_ui','survey_id'])) {
            this.props.dispatch({type: "attr_set", attr: ['isOfflineMode'], data: false});
            this.props.dispatch({type: "attr_set", attr: ['chatLiveData','status_sub'], data: STATUS_SUB_SURVEY_SHOW});
            return;
        }

        let surveyMode = false;
        let navigateToSurvey = false;
        let tipMode = false;

        let surveyByVisitor = (this.props.chatwidget.hasIn(['chatLiveData','status_sub']) && (this.props.chatwidget.getIn(['chatLiveData','status_sub']) == STATUS_SUB_CONTACT_FORM || this.props.chatwidget.getIn(['chatLiveData','status_sub']) == STATUS_SUB_SURVEY_SHOW || (this.props.chatwidget.getIn(['chatLiveData','status_sub']) == STATUS_SUB_USER_CLOSED_CHAT && (
            this.props.chatwidget.getIn(['chatLiveData','uid']) > 0 ||
            this.props.chatwidget.getIn(['chatLiveData','status']) === STATUS_BOT_CHAT ||
            this.props.chatwidget.getIn(['chatLiveData','status']) == STATUS_CLOSED_CHAT
        ))));
        
        let surveyByOperator = (this.props.chatwidget.getIn(['chatLiveData','status']) == STATUS_CLOSED_CHAT && this.props.chatwidget.getIn(['chatLiveData','uid']) > 0);

        if ((surveyByVisitor == true || surveyByOperator) && this.props.chatwidget.hasIn(['chat_ui','survey_id'])) {

            // If survey button is required and we have not went to survey yet
            if ((!this.props.chatwidget.hasIn(['chat_ui','survey_button']) || this.props.chatwidget.getIn(['chat_ui_state','show_survey']) === 1) || surveyByVisitor == true) {
                surveyMode = true;
            } else {
                navigateToSurvey = true;
            }
        }

        // User has to confirm close
        if (surveyMode === false && this.props.chatwidget.hasIn(['chat_ui','confirm_close']) && this.props.chatwidget.getIn(['chat_ui_state','confirm_close']) === 0) {
            this.props.dispatch({'type' : 'UI_STATE', 'data' : {'attr': 'confirm_close', 'val': 1}});
            return;
        }

        // User confirmed to close
        if (this.props.chatwidget.getIn(['chat_ui_state','confirm_close']) === 1) {
            this.props.dispatch({'type' : 'UI_STATE', 'data' : {'attr': 'confirm_close', 'val': 2}});
        }

        // User has confirmed/or denied pre-survey
        if (this.props.chatwidget.getIn(['chat_ui_state','pre_survey_done']) === 1) {
            this.props.dispatch({'type' : 'UI_STATE', 'data' : {'attr': 'pre_survey_done', 'val': 2}});
        }

        if (this.props.chatwidget.hasIn(['chat_ui','pre_survey_url']) && this.props.chatwidget.getIn(['chat_ui_state','pre_survey_done']) === 0 && this.props.chatwidget.getIn(['chatLiveData','uid']) > 0) {
            this.props.dispatch({'type' : 'UI_STATE', 'data' : {'attr': 'pre_survey_done', 'val': 1}});
            tipMode = true;
        }

        if (navigateToSurvey === true) {
            // Forward user to survey on close
            // Means chat was closed by operator but visitor is still not in survey mode
            this.props.dispatch({'type' : 'UI_STATE', 'data' : {'attr': 'show_survey', 'val': 1}});
            return;
        }

        if (this.props.chatwidget.get('initClose') === false && this.props.chatwidget.hasIn(['chat_ui','survey_id']) && surveyMode == false && (this.props.chatwidget.getIn(['chatLiveData','uid']) > 0 || (!this.props.chatwidget.hasIn(['chat_ui','hide_survey_bot']) && this.props.chatwidget.getIn(['chatLiveData','status']) === STATUS_BOT_CHAT))) {
            this.props.dispatch(endChat({'show_start' : (params && params['show_start'] ? params['show_start'] : false),'noCloseReason' : 'SHOW_SURVEY', 'noClose' : true, 'vid' : this.props.chatwidget.get('vid'), 'chat': {id : this.props.chatwidget.getIn(['chatData','id']), hash : this.props.chatwidget.getIn(['chatData','hash'])}}));
        } else if (tipMode == false) {
            this.props.dispatch(endChat({'show_start' : (params && params['show_start'] ? params['show_start'] : false),'vid' : this.props.chatwidget.get('vid'), 'chat': {id : this.props.chatwidget.getIn(['chatData','id']), hash : this.props.chatwidget.getIn(['chatData','hash'])}}));
        }
    }

    popupChat() {

        var eventEmiter = null;

        if (window.parent && window.parent['$_'+helperFunctions.prefixUppercase] && window.parent.closed === false) {
            eventEmiter = window.parent['$_'+helperFunctions.prefixUppercase].eventListener;
        } else if (window.opener && window.opener['$_'+helperFunctions.prefixUppercase] && window.opener.closed === false) {
            eventEmiter = window.opener['$_'+helperFunctions.prefixUppercase].eventListener;
        }

        if (eventEmiter !== null) {
            eventEmiter.emitEvent('openPopup');
        } else {
            helperFunctions.sendMessageParent('openPopup', []);
        }
    }

    switchColumn() {
        let positionPlacement = this.props.chatwidget.get('position_placement').includes('full_height_') ? this.props.chatwidget.get('position_placement_original') : "full_height" + (this.props.chatwidget.get('position_placement_original').includes('_right') ? '_right' : '_left');
        helperFunctions.sendMessageParent('widgetHeight', [{"position_placement": positionPlacement}]);
        this.props.dispatch({'type' : 'position_placement', 'data' : positionPlacement});
    }

    setProfile(profile) {
        this.profileHTML = profile;
    }

    setMessages(messages) {
        this.messagesHTML = messages;
    }

    setHideMessageField(hide) {
        this.hideMessageField = hide;
    }

    render() {

        if (this.props.chatwidget.get('loadedCore') === false) {
            return null;
        }

        if (this.props.chatwidget.getIn(['proactive','pending']) === true) {
            return  <Suspense fallback="..."><ProactiveInvitation setBotPayload={this.setBotPayload} /></Suspense>
        }

        var className = 'd-flex flex-column flex-grow-1 reset-container-margins';

        if (this.props.chatwidget.get('mode') == 'widget') {
            className = className + (this.props.chatwidget.get('isMobile') == true ? ' mobile-body' : ' desktop-body');
        } else if (this.props.chatwidget.get('mode') == 'embed') {
            className = className + (this.props.chatwidget.get('isMobile') == true ? ' mobile-embed-body' : ' desktop-embed-body');
        }

        if (this.props.chatwidget.hasIn(['chat_ui','msg_expand']) && this.props.chatwidget.get('mode') == 'embed') {
            className += " mh-100";
        }

        if (this.props.chatwidget.get('isChatting') === true && this.props.chatwidget.get('isOfflineMode') === false) {
            className += " online-chat online-chat-status-" + this.props.chatwidget.getIn(['chatLiveData','status']);
            if (this.props.chatwidget.getIn(['onlineData','fetched']) === false && this.props.chatwidget.get('initLoaded') === false) {
                className += " hide";
            }
            return (<React.Fragment>
                {this.props.chatwidget.hasIn(['chat_ui','custom_html_header']) && (this.props.chatwidget.getIn(['onlineData','fetched']) === true || this.props.chatwidget.get('initLoaded') === true) && <div className="lhc-custom-header-above" dangerouslySetInnerHTML={{__html:this.props.chatwidget.getIn(['chat_ui','custom_html_header'])}}></div>}
                {(this.props.chatwidget.getIn(['onlineData','fetched']) === true || this.props.chatwidget.get('initLoaded') === true) && this.props.chatwidget.get('mode') == 'widget' && <HeaderChat switchColumn={this.switchColumn} popupChat={this.popupChat} endChat={this.endChat} />}
                <div className={className}><OnlineChat textMessageRef={this.textareaRef} hideMessageField={this.hideMessageField} profileBefore={this.profileHTML} messagesBefore={this.messagesHTML} cancelClose={this.cancelClose} endChat={this.endChat} /></div>
                {this.props.chatwidget.hasIn(['chat_ui','custom_html_footer']) && this.props.chatwidget.getIn(['chat_ui','custom_html_footer']) != '' && (this.props.chatwidget.getIn(['onlineData','fetched']) === true || this.props.chatwidget.get('initLoaded') === true) && ((this.props.chatwidget.hasIn(['chat_ui','chfr']) && <div className="lhc-custom-footer-below" dangerouslySetInnerHTML={{__html:this.props.chatwidget.getIn(['chat_ui','custom_html_footer'])}}></div>) || (<Suspense fallback=""><div className="lhc-custom-footer-below"><CustomHTML setStateParent={(state) => this.setState(state)} attr="custom_html_footer" /></div></Suspense>))}
            </React.Fragment>)
        } else if (this.props.chatwidget.get('isOnline') === true && this.props.chatwidget.get('isOfflineMode') === false) {
            if (!this.props.chatwidget.getIn(['onlineData','fetched']) && this.props.chatwidget.get('chatEnded') === false) {
                className += " hide";
            }
            className += " start-chat";
            return (<React.Fragment>{this.props.chatwidget.hasIn(['chat_ui','custom_html_header']) && (this.props.chatwidget.getIn(['onlineData','fetched']) || this.props.chatwidget.get('chatEnded') === true) && <div className="lhc-custom-header-above" dangerouslySetInnerHTML={{__html:this.props.chatwidget.getIn(['chat_ui','custom_html_header'])}}></div>}
                {(this.props.chatwidget.getIn(['onlineData','fetched']) || this.props.chatwidget.get('chatEnded') === true) && this.props.chatwidget.get('mode') == 'widget' && <HeaderChat switchColumn={this.switchColumn} popupChat={this.popupChat} endChat={this.endChat} />}<div className={className}><StartChat textMessageRef={this.textareaRef} botPayload={this.botPayload} setHideMessageField={this.setHideMessageField} setProfile={this.setProfile} setMessages={this.setMessages} /></div>
                {this.props.chatwidget.hasIn(['chat_ui','custom_html_footer']) && this.props.chatwidget.getIn(['onlineData','fetched']) && this.props.chatwidget.getIn(['chat_ui','custom_html_footer']) != '' && ((this.props.chatwidget.hasIn(['chat_ui','chfr']) && <div className="lhc-custom-footer-below" dangerouslySetInnerHTML={{__html:this.props.chatwidget.getIn(['chat_ui','custom_html_footer'])}}></div>) || (<Suspense fallback=""><div className="lhc-custom-footer-below"><CustomHTML setStateParent={(state) => this.setState(state)} attr="custom_html_footer" /></div></Suspense>))}
            </React.Fragment>)
        } else {
            className += " offline-chat";
            return (<React.Fragment>{this.props.chatwidget.hasIn(['chat_ui','custom_html_header']) && <div className="lhc-custom-header-above" dangerouslySetInnerHTML={{__html:this.props.chatwidget.getIn(['chat_ui','custom_html_header'])}}></div>}{this.props.chatwidget.get('mode') == 'widget' && <HeaderChat switchColumn={this.switchColumn} popupChat={this.popupChat} endChat={this.endChat} />}<div className={className}><Suspense fallback=""><OfflineChat /></Suspense></div>
                {this.props.chatwidget.hasIn(['chat_ui','custom_html_footer']) && this.props.chatwidget.getIn(['chat_ui','custom_html_footer']) != '' && ((this.props.chatwidget.hasIn(['chat_ui','chfr']) && <div className="lhc-custom-footer-below" dangerouslySetInnerHTML={{__html:this.props.chatwidget.getIn(['chat_ui','custom_html_footer'])}}></div>) || (<Suspense fallback=""><div className="lhc-custom-footer-below"><CustomHTML setStateParent={(state) => this.setState(state)} attr="custom_html_footer" /></div></Suspense>))}
            </React.Fragment>)
        }
    }
}

export default BodyChat;
