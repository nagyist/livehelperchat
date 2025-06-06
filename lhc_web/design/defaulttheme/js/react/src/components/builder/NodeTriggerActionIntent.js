import React, { Component } from 'react';
import NodeTriggerActionType from './NodeTriggerActionType';
import NodeTriggerList from './NodeTriggerList';
import NodeActionIntentItem from './intent/NodeActionIntentItem';
import shortid from 'shortid';

class NodeTriggerActionIntent extends Component {

    constructor(props) {
        super(props);
        this.changeType = this.changeType.bind(this);
        this.removeAction = this.removeAction.bind(this);
        this.onchangeAttr = this.onchangeAttr.bind(this);
        this.addIntent = this.addIntent.bind(this);
        this.onDeleteField = this.onDeleteField.bind(this);
        this.onchangeFieldAttr = this.onchangeFieldAttr.bind(this);
    }

    changeType(e) {
        this.props.onChangeType({id : this.props.id, 'type' : e.target.value});
    }

    removeAction() {
        this.props.removeAction({id : this.props.id});
    }

    onchangeAttr(e) {
        this.props.onChangeContent({id : this.props.id, 'path' : ['content'].concat(e.path), value : e.value});
    }

    onchangeFieldAttr(e) {
        this.props.onChangeContent({id : this.props.id, 'path' : ['content','intents',e.id].concat(e.path), value : e.value});
    }

    addIntent() {
        this.props.addSubelement({id : this.props.id, 'path' : ['content','intents'], 'default' : {'_id': shortid.generate(), 'type' : 'intent', content : {'words' : ''}}});
    }

    onDeleteField(fieldIndex) {
        this.props.deleteSubelement({id : this.props.id, 'path' : ['content','intents',fieldIndex]});
    }

    render() {

        var button_list = [];

        if (this.props.action.hasIn(['content','intents'])) {
            button_list = this.props.action.getIn(['content','intents']).map((field, index) => {
                return <NodeActionIntentItem id={index} isFirst={index == 0} isLast={index +1 == this.props.action.getIn(['content','intents']).size} key={field.get('_id')} action={field} onMoveDownField={this.onMoveDownField} onMoveUpField={this.onMoveUpField} onDeleteField={this.onDeleteField} onChangeFieldAttr={this.onchangeFieldAttr}/>
            });
        }

        return (
            <div>
                <div className="d-flex flex-row">
                    <div>
                        <div className="btn-group float-start" role="group" aria-label="Trigger actions">
                            <button disabled="disabled" className="btn btn-sm btn-info">{this.props.id + 1}</button>
                            {this.props.isFirst == false && <button className="btn btn-secondary btn-sm" onClick={(e) => this.props.upField(this.props.id)}><i className="material-icons me-0">keyboard_arrow_up</i></button>}
                            {this.props.isLast == false && <button className="btn btn-secondary btn-sm" onClick={(e) => this.props.downField(this.props.id)}><i className="material-icons me-0">keyboard_arrow_down</i></button>}
                        </div>
                    </div>
                    <div className="flex-grow-1 px-2">
                        <NodeTriggerActionType onChange={this.changeType} type={this.props.action.get('type')} />
                    </div>
                    <div className="pe-2">
                        <div className="input-group input-group-sm">
                            <span className="input-group-text" id="basic-addon1"><span className="material-icons me-0">filter_alt</span></span>
                            <input type="text" title="Bot condition - action will only execute if this condition matches. Use prefix (-) for negation. Examples: banned_user OR -banned_user" onChange={(e) => this.onchangeAttr({'path' : ['trigger_condition'], 'value' : e.target.value})} defaultValue={this.props.action.getIn(['content','trigger_condition'])} className="form-control form-control-sm" placeholder="vip_1 OR -vip_1" />
                        </div>
                    </div>
                    <div className="pe-2">
                        <div className="input-group input-group-sm">
                            <span className="input-group-text" id="basic-addon1"><span className="material-icons me-0">vpn_key</span></span>
                            <input type="text" className="form-control" readOnly={true} value={this.props.action.getIn(['_id'])} title="Action ID"/>
                        </div>
                    </div>
                    <div className="pe-2 pt-1 text-nowrap">
                        <label className="form-check-label" title="Response will not be executed. Usefull for a quick testing."><input onChange={(e) => this.props.onChangeContent({id : this.props.id, 'path' : ['skip_resp'], value : e.target.checked})} defaultChecked={this.props.action.getIn(['skip_resp'])} type="checkbox"/> Skip</label>
                    </div>
                    <div>
                        <button onClick={this.removeAction} type="button" className="btn btn-danger btn-sm float-end">
                            <i className="material-icons me-0">delete</i>
                        </button>
                    </div>
                </div>
                <div className="row">
                    <div className="col-12">
                        <div className="btn-group float-end" role="group">
                            <button onClick={this.addIntent} className="btn btn-sm btn-secondary"><i className="material-icons me-0">add</i> Add intent detection</button>
                        </div>
                    </div>
                </div>
                {button_list}

                <hr className="hr-big" />

            </div>
        );
    }
}

export default NodeTriggerActionIntent;
