import { Link } from "react-router-dom";
import { KeyRound, Languages, Text } from "lucide-react";
import { useLanguage } from "../../../context/LanguageContext";

export default function ApanelTranslations() {
  const { t } = useLanguage();
  const tools = [
    {
      to: "/apanel/translation-keys",
      title: t("apanel.translationManagement.keysTitle"),
      description: t("apanel.translationManagement.keysDescription"),
      icon: KeyRound,
    },
    {
      to: "/apanel/translation-values",
      title: t("apanel.translationManagement.valuesTitle"),
      description: t("apanel.translationManagement.valuesDescription"),
      icon: Text,
    },
  ];

  return (
    <div className="space-y-6 animate-in fade-in duration-200">
      <div>
        <h1 className="text-xl font-extrabold text-navy uppercase tracking-wider">
          {t("apanel.translationManagement.title")}
        </h1>
        <p className="text-gray-400 text-xs font-semibold">
          {t("apanel.translationManagement.subtitle")}
        </p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {tools.map((tool) => {
          const Icon = tool.icon;
          return (
            <Link
              key={tool.to}
              to={tool.to}
              className="bg-white border border-gray-100 rounded-2xl p-5 shadow-xs hover:border-primary/40 transition-all"
            >
              <div className="flex items-start gap-4">
                <div className="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                  <Icon className="w-5 h-5" />
                </div>
                <div className="space-y-1">
                  <h2 className="text-sm font-extrabold text-navy uppercase tracking-wider">
                    {tool.title}
                  </h2>
                  <p className="text-xs font-semibold text-gray-400 leading-relaxed">
                    {tool.description}
                  </p>
                </div>
              </div>
            </Link>
          );
        })}
      </div>

      <div className="bg-white border border-gray-100 rounded-2xl p-5 shadow-xs flex items-start gap-4">
        <Languages className="w-5 h-5 text-primary shrink-0 mt-0.5" />
        <p className="text-xs font-semibold text-gray-500 leading-relaxed">
          {t("apanel.translationManagement.routeNote")}
        </p>
      </div>
    </div>
  );
}
