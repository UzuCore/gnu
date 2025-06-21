/**
 * @license Copyright (c) 2003-2024, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */
import { ClassicEditor as ClassicEditorBase } from '@ckeditor/ckeditor5-editor-classic';
import { Essentials } from '@ckeditor/ckeditor5-essentials';
import { Autoformat } from '@ckeditor/ckeditor5-autoformat';
import { Paragraph } from '@ckeditor/ckeditor5-paragraph';
import { Heading } from '@ckeditor/ckeditor5-heading';
import { TextTransformation } from '@ckeditor/ckeditor5-typing';
import { Bold, Italic, Underline, Strikethrough } from '@ckeditor/ckeditor5-basic-styles';
import { FontColor, FontBackgroundColor } from '@ckeditor/ckeditor5-font';
import { Highlight } from '@ckeditor/ckeditor5-highlight';
import { RemoveFormat } from '@ckeditor/ckeditor5-remove-format';
import { Link } from '@ckeditor/ckeditor5-link';
import { List } from '@ckeditor/ckeditor5-list';
import { Alignment } from '@ckeditor/ckeditor5-alignment';
import { Image, ImageCaption, ImageStyle, ImageToolbar, ImageUpload, PictureEditing } from '@ckeditor/ckeditor5-image';
import { MediaEmbed } from '@ckeditor/ckeditor5-media-embed';
import { Table, TableToolbar } from '@ckeditor/ckeditor5-table';
import { CodeBlock } from '@ckeditor/ckeditor5-code-block';
import { BlockQuote } from '@ckeditor/ckeditor5-block-quote';
import { HorizontalLine } from '@ckeditor/ckeditor5-horizontal-line';
import { SourceEditing } from '@ckeditor/ckeditor5-source-editing';
import { SpecialCharacters } from '@ckeditor/ckeditor5-special-characters';
import { Mention } from '@ckeditor/ckeditor5-mention';
import { PasteFromOffice } from '@ckeditor/ckeditor5-paste-from-office';
import { WordCount } from '@ckeditor/ckeditor5-word-count';
import { Indent } from '@ckeditor/ckeditor5-indent';
export default class ClassicEditor extends ClassicEditorBase {
    static builtinPlugins: (typeof TextTransformation | typeof Essentials | typeof Paragraph | typeof Heading | typeof Autoformat | typeof Bold | typeof Italic | typeof Strikethrough | typeof Underline | typeof FontBackgroundColor | typeof FontColor | typeof Highlight | typeof RemoveFormat | typeof Image | typeof ImageCaption | typeof ImageStyle | typeof ImageToolbar | typeof ImageUpload | typeof Link | typeof List | typeof Alignment | typeof MediaEmbed | typeof Table | typeof TableToolbar | typeof CodeBlock | typeof BlockQuote | typeof HorizontalLine | typeof SourceEditing | typeof SpecialCharacters | typeof Mention | typeof PasteFromOffice | typeof WordCount | typeof Indent | typeof PictureEditing)[];
    static defaultConfig: {
        toolbar: {
            items: string[];
        };
        image: {
            toolbar: string[];
        };
        table: {
            contentToolbar: string[];
        };
        language: string;
    };
}
