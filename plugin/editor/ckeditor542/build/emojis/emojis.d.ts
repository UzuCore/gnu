import { Plugin, type Editor } from 'ckeditor5/src/core.js';
import { Typing } from 'ckeditor5/src/typing.js';
import '../theme/emojis.css';
export default class Emojis extends Plugin {
    private _characters;
    private _groups;
    private _allEmojisGroupLabel;
    static get requires(): readonly [typeof Typing];
    static get pluginName(): "Emojis";
    constructor(editor: Editor);
    init(): void;
    addItems(groupName: string, items: Array<EmojiDefinition>, options?: {
        label: string;
    }): void;
    getGroups(): Set<string>;
    getCharactersForGroup(groupName: string): Set<string> | undefined;
    getCharacter(title: string): string | undefined;
    private _getGroup;
    private _updateGrid;
    private _createDropdownPanelContent;
}
export interface EmojiDefinition {
    title: string;
    character: string;
}
