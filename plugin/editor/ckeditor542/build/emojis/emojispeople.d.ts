/**
 * @license Copyright (c) 2003-2024, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */
/**
 * @module emojis/emojispeople
 */
import { Plugin } from 'ckeditor5/src/core.js';
/**
 * A plugin that provides special characters for the "People" category.
 *
 * ```ts
 * ClassicEditor
 *   .create( {
 *     plugins: [ ..., Emojis, EmojisPeople ],
 *   } )
 *   .then( ... )
 *   .catch( ... );
 * ```
 */
export default class EmojisPeople extends Plugin {
    /**
     * @inheritDoc
     */
    static get pluginName(): "EmojisPeople";
    /**
     * @inheritDoc
     */
    init(): void;
}
