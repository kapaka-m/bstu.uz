export function formatLocalizedDate(value, locale, _translate, options = {}) {
  if (!value) return "";
  if (/^\d{4}$/.test(String(value).trim())) return String(value);

  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return String(value);

  const monthStyle = options.month === "long" ? "long" : "short";
  const dateText = new Intl.DateTimeFormat(locale || undefined, {
    day: options.day || "numeric",
    month: monthStyle,
    year: "numeric",
  }).format(parsed);

  if (!options.hour && !options.minute) {
    return dateText;
  }

  const timeText = [
    options.hour ? String(parsed.getHours()).padStart(2, "0") : null,
    options.minute ? String(parsed.getMinutes()).padStart(2, "0") : null,
  ]
    .filter(Boolean)
    .join(":");

  return timeText ? `${dateText} ${timeText}` : dateText;
}
