const technicalLabels = new Set(["CMS", "LTR", "RTL"]);
const textAttributes = new Set(["title", "placeholder", "aria-label", "alt"]);

export default {
  meta: {
    type: "problem",
    schema: [],
    messages: {
      untranslated: "Store visible text in the CMS translation tables and render it with t().",
    },
  },
  create(context) {
    const check = (node, value) => {
      const text = value.trim().replace(/\s+/g, " ");
      if (!/[A-Za-z]{3}/.test(text) || technicalLabels.has(text)) return;
      if (/^(?:\/|https?:\/\/|cms\/media-library\/)/.test(text)) return;
      context.report({ node, messageId: "untranslated" });
    };
    return {
      JSXText(node) {
        check(node, node.value);
      },
      JSXAttribute(node) {
        if (textAttributes.has(node.name.name) && typeof node.value?.value === "string") {
          check(node, node.value.value);
        }
      },
    };
  },
};
