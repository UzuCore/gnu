/**
 * @license Copyright (c) 2003-2024, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */
/**
 * @module emojis/emojisplaces
 */
import { Plugin } from 'ckeditor5/src/core.js';
/**
 * A plugin that provides special characters for the "Places" category.
 *
 * ```ts
 * ClassicEditor
 *   .create( {
 *     plugins: [ ..., Emojis, EmojisPlaces ],
 *   } )
 *   .then( ... )
 *   .catch( ... );
 * ```
 */
export default class EmojisPlaces extends Plugin {
    /**
     * @inheritDoc
     */
    static get pluginName(): "EmojisPlaces";
    /**
     * @inheritDoc
     */
    init(): void;
}
