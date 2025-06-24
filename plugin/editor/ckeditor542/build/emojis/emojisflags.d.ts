/**
 * @license Copyright (c) 2003-2024, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */
/**
 * @module emojis/emojisflags
 */
import { Plugin } from 'ckeditor5/src/core.js';
/**
 * A plugin that provides special characters for the "Flags" category.
 *
 * ```ts
 * ClassicEditor
 *   .create( {
 *     plugins: [ ..., Emojis, EmojisFlags ],
 *   } )
 *   .then( ... )
 *   .catch( ... );
 * ```
 */
export default class EmojisFlags extends Plugin {
    /**
     * @inheritDoc
     */
    static get pluginName(): "EmojisFlags";
    /**
     * @inheritDoc
     */
    init(): void;
}
