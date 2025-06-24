/**
 * @license Copyright (c) 2003-2024, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */
import type { Emojis, EmojisActivities, EmojisFlags, EmojisFood, EmojisNature, EmojisObjects, EmojisPeople, EmojisPlaces, EmojisSymbols, EmojisConfig } from './index.js';
declare module '@ckeditor/ckeditor5-core' {
    interface PluginsMap {
        [Emojis.pluginName]: Emojis;
        [EmojisPeople.pluginName]: EmojisPeople;
        [EmojisNature.pluginName]: EmojisNature;
        [EmojisPlaces.pluginName]: EmojisPlaces;
        [EmojisFood.pluginName]: EmojisFood;
        [EmojisActivities.pluginName]: EmojisActivities;
        [EmojisObjects.pluginName]: EmojisObjects;
        [EmojisFlags.pluginName]: EmojisFlags;
        [EmojisSymbols.pluginName]: EmojisSymbols;
    }
    interface EditorConfig {
        /**
         * The configuration of the {@link module:emojis/emojis~Emojis} feature.
         *
         * Read more in {@link module:emojis/emojisconfig~EmojisConfig}.
         */
        emojis?: EmojisConfig;
    }
}
