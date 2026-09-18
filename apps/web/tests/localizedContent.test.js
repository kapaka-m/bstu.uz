import assert from "node:assert/strict";
import test from "node:test";
import { getLocalizedValue, selectTranslation } from "../src/lib/localizedContent.js";

const item = { translations: [
  { locale: "en", name: "Head of Department" },
  { locale: "uz", name: "Kafedra mudiri" },
  { locale: "ru", name: "Заведующий кафедрой" },
  { locale: "ar", name: "رئيس القسم" },
] };

test("cards and nested table cells follow the selected language, independent of row order", () => {
  for (const { locale, name } of item.translations) {
    assert.equal(selectTranslation(item, locale).name, name);
    assert.equal(getLocalizedValue({ program: item }, "program.translations.0.name", locale), name);
  }
});

test("ordinary arrays, missing relationships, and fallback behavior remain supported", () => {
  assert.equal(getLocalizedValue({ items: [{ name: "value" }] }, "items.0.name", "ar"), "value");
  assert.equal(getLocalizedValue({}, "program.translations.0.name", "uz"), undefined);
  assert.deepEqual(selectTranslation(null, "ar"), {});
  assert.equal(selectTranslation(item, "missing").name, "Head of Department");
});
