/**
 * @license Copyright (c) 2003-2024, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */
/**
 * @module special-characters/ui/characterinfoview
 */
import type { Locale } from 'ckeditor5/src/utils.js';
import { View } from 'ckeditor5/src/ui.js';
import '../../theme/characterinfo.css';
/**
 * The view displaying detailed information about a special character glyph, e.g. upon
 * hovering it with a mouse.
 */
export default class CharacterInfoView extends View<HTMLDivElement> {
    /**
     * The character whose information is displayed by the view. For instance, "😃".
     *
     * @observable
     */
    character: string | null;
    /**
     * The name of the {@link #character}. For instance, "Grinning Face".
     *
     * @observable
     */
    name: string | null;
    /**
     * The "Unicode string" of the {@link #character}. For instance "U+1F603".
     *
     * @observable
     */
    readonly code: string;
    constructor(locale: Locale);
}
