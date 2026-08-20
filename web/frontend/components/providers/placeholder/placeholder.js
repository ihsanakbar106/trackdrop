// placeholder/placeholder.js

import { Plugin } from "ckeditor5";

import PlaceholderEditing from "./placeholderediting";
import PlaceholderUI from "./placeholderui";

export default class Placeholder extends Plugin {
  static get requires() {
    return [PlaceholderEditing, PlaceholderUI];
  }
}
