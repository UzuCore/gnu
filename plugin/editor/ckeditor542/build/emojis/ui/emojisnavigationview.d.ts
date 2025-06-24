/**
 * @license Copyright (c) 2003-2024, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */
/**
 * @module emojis/ui/emojisnavigationview
 */
import { type Locale } from 'ckeditor5/src/utils.js';
import { FormHeaderView, type DropdownView } from 'ckeditor5/src/ui.js';
/**
 * A class representing the navigation part of the emojis UI. It is responsible
 * for describing the feature and allowing the user to select a particular emoji group.
 */
export default class EmojisNavigationView extends FormHeaderView {
    /**
     * A dropdown that allows selecting a group of emojis to be displayed.
     */
    groupDropdownView: GroupDropdownView;
    /**
     * Creates an instance of the {@link module:emojis/ui/EmojisNavigationView~EmojisNavigationView}
     * class.
     *
     * @param locale The localization services instance.
     * @param groupNames The names of the emoji groups and their displayed labels.
     */
    constructor(locale: Locale, groupNames: GroupNames);
    /**
     * Returns the name of the emoji group currently selected in the {@link #groupDropdownView}.
     */
    get currentGroupName(): string;
    /**
     * Focuses the emoji categories dropdown.
     */
    focus(): void;
    /**
     * Returns a dropdown that allows selecting emoji groups.
     *
     * @param groupNames The names of the emoji groups and their displayed labels.
     */
    private _createGroupDropdown;
    /**
     * Returns list item definitions to be used in the emoji group dropdown
     * representing specific emoji groups.
     *
     * @param dropdown Dropdown view element
     * @param groupNames The names of the emoji groups and their displayed labels.
     */
    private _getEmojiGroupListItemDefinitions;
}
/**
 * The names of the emoji groups and their displayed labels.
 */
export type GroupNames = Map<string, string>;
/**
 * `DropdownView` with additional field for the name of the currectly selected emoji group.
 */
export type GroupDropdownView = DropdownView & {
    value: string;
};
