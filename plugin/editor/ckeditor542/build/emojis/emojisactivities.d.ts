/**
 * @license Copyright (c) 2003-2024, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */
/**
 * @module emojis/emojisactivities
 */
import { Plugin } from 'ckeditor5/src/core.js';
/**
 * A plugin that provides special characters for the "Activities" category.
 *
 * ```ts
 * ClassicEditor
 *   .create( {
 *     plugins: [ ..., Emojis, EmojisActivities ],
 *   } )
 *   .then( ... )
 *   .catch( ... );
 * ```
 */
export default class EmojisActivities extends Plugin {
    /**
     * @inheritDoc
     */
    static get pluginName(): "EmojisActivities";
    /**
     * @inheritDoc
     */
    init(): void;
}
