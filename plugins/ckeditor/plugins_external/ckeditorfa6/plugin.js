CKEDITOR.plugins.add('ckeditorfa6',{icons:'ckeditorfa',
init:function(editor){
editor.addCommand('ckeditorfa6', new CKEDITOR.dialogCommand('ckeditorFaDialog',{allowedContent:'span(!fa-*)'}));
editor.ui.addButton('ckeditorfa6',{label:'FontAwesome6 icons',command:'ckeditorfa6',toolbar:'insert',icon:this.path + 'icons/ckeditorfa.png'});
CKEDITOR.dialog.add('ckeditorFaDialog', this.path + 'dialogs/ckeditorfa6.js');
CKEDITOR.document.appendStyleSheet(this.path + 'css/ckeditorfa6.css');
}
});