YUI.add("moodle-local_friadmin-courselist",function(e,t){M.local_friadmin=M.local_friadmin||{},M.local_friadmin.courselist={CSS:{},SELECTORS:{COURSEFILTER_FORM:"#mform-coursefilter",SEL_MUNICIPALITY:"id_selmunicipality",SEL_SELECTOR:"id_selsector",SEL_LOCATION:"id_sellocation",CHANGE_ELEMENTS:"#id_selmunicipality, #id_selsector",SUBMIT_BTN:"#id_submitbutton"},form:null,ajaxurl:M.cfg.wwwroot+"/local/friadmin/ajax/courselist.php",init:function(){this.form=e.one(this.SELECTORS.COURSEFILTER_FORM),this.form&&this.form.delegate("valuechange",this.value_changed,this.SELECTORS.CHANGE_ELEMENTS,this)},value_changed:function(t){var n=t.target.getAttribute("id"),r,i={};n===this.SELECTORS.SEL_MUNICIPALITY?(i={municipalityid:t.newVal},this.performAjaxAction("municipalitychange",i,this.municipaltiy_change,this)):n===this.SELECTORS.SEL_SELECTOR&&(r=e.one("#"+this.SELECTORS.SEL_MUNICIPALITY).get("value"),i={municipalityid:r,sectorid:t.newVal},this.performAjaxAction("sectorchange",i,this.sector_change,this))},municipaltiy_change:function(e,t,n){var r=this.checkAjaxResponse(e,t,n);this.change_menu(this.SELECTORS.SEL_SELECTOR,r.outcome.sector),this.change_menu(this.SELECTORS.SEL_LOCATION,r.outcome.location)},sector_change:function(e,t,n){var r=this.checkAjaxResponse(e,t,n);this.change_menu(this.SELECTORS.SEL_LOCATION,r.outcome.location)},change_menu:function(t,n){var r=e.one("#"+t),i=0;r&&(r.all("option").each(function(e){i===0?i++:e.remove()}),e.Object.each(n,function(t,n){e.Node.create('<option value="'+n+'">'+t+"</option>").appendTo(r)}))},performAjaxAction:function(t,n,r,i){var s=new e.IO;n.action=t,n.ajax="1",n.sesskey=M.cfg.sesskey,r===null&&(r=function(){}),s.send(this.ajaxurl,{method:"POST",on:{complete:r},context:i,data:build_querystring(n),arguments:n})},checkAjaxResponse:function(t,n,r){if(n.status!==200)return!1;if(t===null||r===null)return!1;var i=e.JSON.parse(n.responseText);return i.error!==!1&&new M.core.exception(i),i.outcome===!1?!1:i}}},"@VERSION@",{requires:["base","node","event","event-valuechange","node-event-delegate","io-form","selector-css3"]});