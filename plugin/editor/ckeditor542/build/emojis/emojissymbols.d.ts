/**
 * @license Copyright (c) 2003-2024, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */
/**
 * @module emojis/emojissymbols
 */
import { Plugin } from 'ckeditor5/src/core.js';
/**
 * A plugin that provides special characters for the "Symbols" category.
 *
 * ```ts
 * ClassicEditor
 *   .create( {
 *     plugins: [ ..., Emojis, EmojisSymbols ],
 *   } )
 *   .then( ... )
 *   .catch( ... );
 * ```
 */
export default class EmojisSymbols extends Plugin {
    /**
     * @inheritDoc
     */
    static get pluginName(): "EmojisSymbols";
    /**
     * @inheritDoc
     */
    init(): void;
}
