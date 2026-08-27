const UZBEK_MONTHS = {
  short: [
    "yan",
    "fev",
    "mar",
    "apr",
    "may",
    "iyun",
    "iyul",
    "avg",
    "sen",
    "okt",
    "noy",
    "dek",
  ],
  long: [
    "yanvar",
    "fevral",
    "mart",
    "aprel",
    "may",
    "iyun",
    "iyul",
    "avgust",
    "sentabr",
    "oktabr",
    "noyabr",
    "dekabr",
  ],
};

const isUzbekLocale = (locale) =>
  String(locale || "").toLowerCase().startsWith("uz");

export function formatLocalizedDate(value, locale, _translate, options = {}) {
  if (!value) return "";
  if (/^\d{4}$/.test(String(value).trim())) return String(value);

  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return String(value);

  const monthStyle = options.month === "long" ? "long" : "short";
  const dayStyle = options.day || "numeric";

  if (isUzbekLocale(locale)) {
    const day = parsed.getDate();
    const dayText = dayStyle === "2-digit" ? String(day).padStart(2, "0") : String(day);
    const month = UZBEK_MONTHS[monthStyle][parsed.getMonth()];
    const dateText = `${parsed.getFullYear()}-yil ${dayText}-${month}`;

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

  const dateText = new Intl.DateTimeFormat(locale || undefined, {
    day: dayStyle,
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
