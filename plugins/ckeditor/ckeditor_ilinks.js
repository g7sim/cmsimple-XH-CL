/*
 * @version $Id: ckeditor_ilinks.js 248 2017-04-28 14:29:03Z hi $
 *
 */
CKEDITOR.on("dialogDefinition",function(b){var c=b.data.name;var d=b.data.definition;if(c=="link"){var a=d.getContents("info");a.add({type:"vbox",id:"localPageOptions",children:[{type:"select",label:"CMSimple-Links",id:"localPage",title:"CMSimple-Links",items:CMSLinkList,onChange:function(f){var g=CKEDITOR.dialog.getCurrent();var e=g.getContentElement("info","url");e.setValue(f.data.value)}}]});d.onFocus=function(){var e=this.getContentElement("info","url");e.select()}}});