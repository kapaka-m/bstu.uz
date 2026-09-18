import { RuleTester } from "eslint";
import rule from "../eslint-rules/no-untranslated-jsx.js";

const tester = new RuleTester({ languageOptions: { parserOptions: { ecmaFeatures: { jsx: true } } } });
tester.run("no-untranslated-jsx", rule, {
  valid: [
    '<button>{t("button.save")}</button>',
    '<span>CMS</span>',
    '<a>/apanel/staff</a>',
    '<input placeholder="cms/media-library/example.jpg" />',
    '<span>{profile.position}</span>',
  ],
  invalid: [
    { code: '<button>Save Staff</button>', errors: [{ messageId: "untranslated" }] },
    { code: '<input placeholder="Search staff" />', errors: [{ messageId: "untranslated" }] },
    { code: '<button aria-label="Open menu" />', errors: [{ messageId: "untranslated" }] },
  ],
});
