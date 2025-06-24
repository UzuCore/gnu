/**
 * @license Copyright (c) 2003-2024, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */
/**
 * @module emojis/emojisconfig
 */
/**
 * The configuration of the special characters feature.
 *
 * Read more about {@glink features/emojis#configuration configuring the special characters feature}.
 *
 * ```ts
 * ClassicEditor
 *   .create( editorElement, {
 *     emojis: ... // Emojis feature options.
 *   } )
 *   .then( ... )
 *   .catch( ... );
 * ```
 *
 * See {@link module:core/editor/editorconfig~EditorConfig all editor configuration options}.
 */
export interface EmojisConfig {
    /**
     * The configuration of the special characters category order.
     *
     * Special characters categories are displayed in the UI in the order in which they were registered. Using the `order` property
     * allows to override this behaviour and enforce specific order. Categories not listed in the `order` property will be displayed
     * in the default order below categories listed in the configuration.
     *
     * ```ts
     * ClassicEditor
     *   .create( editorElement, {
     *     plugins: [ Emojis, EmojisActivities, ... ],
     *     Emojis: {
     *       order: [
     *         'People',
     *         'Activities',
     *         'Food',
     *         'Nature',
     *         'Flags'
     *       ]
     *     }
     *   } )
     *   .then( ... )
     *   .catch( ... );
     * ```
     */
    order?: Array<string>;
}
