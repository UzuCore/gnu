CKEDITOR.plugins.add('emoji', {
    icons: 'emoji',
    init: function(editor) {
        editor.addCommand('insertEmoji', new CKEDITOR.dialogCommand('emojiDialog'));
        editor.ui.addButton('EmojiPanel', {
            label: '이모지',
            command: 'insertEmoji',
            toolbar: 'insert',
            icon: 'emoji'  // 스프라이트 이름
        });
        CKEDITOR.dialog.add('emojiDialog', this.path + 'dialogs/emoji.js');
        CKEDITOR.document.appendStyleSheet(this.path + 'styles/emoji.css');
    }
});