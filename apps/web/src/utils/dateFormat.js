const padDay = (value, dayFormat) => {
  const day = String(value);
  return dayFormat === "2-digit" ? day.padStart(2, "0") : day;
};

export function formatLocalizedDate(value, locale, translate, options = {}) {
  if (!value) return "";
  if (/^\d{4}$/.test(String(value).trim())) return String(value);

  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return String(value);

  const day = padDay(parsed.getDate(), options.day || "numeric");
  const year = parsed.getFullYear();
  const monthNumber = parsed.getMonth() + 1;
  const monthStyle = options.month === "long" ? "long" : "short";
  const month = translate?.(`date.months.${monthStyle}.${monthNumber}`) || "";

  const dateText = month
    ? locale === "en"
      ? `${month} ${day}, ${year}`
      : `${day} ${month} ${year}`
    : `${year}-${String(monthNumber).padStart(2, "0")}-${day}`;

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
