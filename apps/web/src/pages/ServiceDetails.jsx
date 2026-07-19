import React, { useEffect, useState } from "react";
import { useParams, Link } from "react-router-dom";
import {
  ArrowLeft,
  CheckCircle,
  HelpCircle,
  FileText,
  Download,
  Loader2,
  Phone,
  Mail,
} from "lucide-react";
import { servicesData } from "../data/mockData";
import PageHeader from "../components/PageHeader";
import { useLanguage } from "../context/LanguageContext";

export default function ServiceDetails() {
  const { id } = useParams();
  const { t, language } = useLanguage();

  // Find dynamic service, but if it doesn't exist (like on fallback route /service-details), use a default
  const service = servicesData.find((s) => s.id === id) || servicesData[0];

  const [downloading, setDownloading] = useState({ pdf: false, doc: false });

  useEffect(() => {
    window.scrollTo(0, 0);
  }, [id]);

  const handleDownload = (type) => {
    setDownloading((prev) => ({ ...prev, [type]: true }));
    setTimeout(() => {
      setDownloading((prev) => ({ ...prev, [type]: false }));
      alert(
        `${t("common.downloadSuccess", "Downloaded Catalog successfully!")}`,
      );
    }, 1500);
  };

  const serviceName = t(`services.${service.id}.name`, service.title);

  const breadcrumbs = [
    { label: t("common.allServices", "Services"), path: "/services" },
    { label: serviceName },
  ];

  return (
    <div className="pt-20 bg-white">
      {/* Page Header Breadcrumbs */}
      <PageHeader
        title={t("common.serviceDetails", "Service Details")}
        breadcrumbs={breadcrumbs}
      />

      <div className="container mx-auto px-4 md:px-8 max-w-7xl py-16 md:py-24">
        {/* Back Button */}
        <div className="mb-10 text-start">
          <Link
            to="/services"
            className="inline-flex items-center gap-2 text-sm font-bold text-primary hover:text-primary-hover group"
          >
            <ArrowLeft
              className={`w-4 h-4 transition-transform ${language === "ar" ? "rotate-180 group-hover:translate-x-1" : "group-hover:-translate-x-1"}`}
            />
            {t("common.backToServices", "Back to Services")}
          </Link>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
          {/* Left Sidebar */}
          <div className="lg:col-span-4 flex flex-col gap-6 text-start">
            {/* Catalog list */}
            <div className="bg-primary-light border border-gray-100 p-8 rounded-3xl">
              <h4 className="text-lg font-extrabold text-navy mb-5 border-b border-gray-200/50 pb-3">
                {t("common.servicesList", "Services List")}
              </h4>
              <ul className="flex flex-col gap-3 font-semibold">
                {servicesData.map((s) => (
                  <li key={s.id}>
                    <Link
                      to={`/services/${s.id}`}
                      className={`block px-4 py-3 rounded-xl text-sm transition-all duration-300 ${
                        s.id === service.id
                          ? "bg-primary text-white shadow-md shadow-primary/20"
                          : "bg-white text-gray-500 hover:bg-gray-100 hover:text-navy"
                      }`}
                    >
                      {t(`services.${s.id}.name`, s.title)}
                    </Link>
                  </li>
                ))}
              </ul>
            </div>

            {/* Download Catalog */}
            <div className="bg-primary-light border border-gray-100 p-8 rounded-3xl">
              <h4 className="text-lg font-extrabold text-navy mb-5 border-b border-gray-200/50 pb-3">
                {t("common.downloadCatalog", "Download Catalog")}
              </h4>
              <div className="flex flex-col gap-3">
                <button
                  onClick={() => handleDownload("pdf")}
                  disabled={downloading.pdf}
                  className="w-full flex items-center justify-between px-4 py-3.5 bg-white hover:bg-gray-50 border border-gray-100 rounded-xl text-sm font-bold text-gray-600 transition-colors shadow-sm cursor-pointer"
                >
                  <span className="flex items-center gap-2">
                    <FileText className="w-4 h-4 text-red-500" />
                    {t("common.catalogPdf", "Catalog PDF")}
                  </span>
                  {downloading.pdf ? (
                    <Loader2 className="w-4 h-4 animate-spin text-primary" />
                  ) : (
                    <Download className="w-4 h-4 text-gray-400" />
                  )}
                </button>

                <button
                  onClick={() => handleDownload("doc")}
                  disabled={downloading.doc}
                  className="w-full flex items-center justify-between px-4 py-3.5 bg-white hover:bg-gray-50 border border-gray-100 rounded-xl text-sm font-bold text-gray-600 transition-colors shadow-sm cursor-pointer"
                >
                  <span className="flex items-center gap-2">
                    <FileText className="w-4 h-4 text-blue-500" />
                    {t("common.catalogDoc", "Catalog DOC")}
                  </span>
                  {downloading.doc ? (
                    <Loader2 className="w-4 h-4 animate-spin text-primary" />
                  ) : (
                    <Download className="w-4 h-4 text-gray-400" />
                  )}
                </button>
              </div>
            </div>

            {/* Help Card */}
            <div className="bg-primary text-white p-8 rounded-3xl flex flex-col items-center text-center shadow-lg shadow-primary/20 relative overflow-hidden">
              <div className="absolute top-0 right-0 w-24 h-24 bg-white/5 rounded-full translate-x-10 -translate-y-10" />
              <HelpCircle className="w-10 h-10 mb-4 text-white/80" />
              <h4 className="text-xl font-bold mb-2">
                {t("common.haveQuestion", "Have a Question?")}
              </h4>
              <p className="text-white/70 text-sm leading-relaxed mb-4">
                {t(
                  "common.contactRegistrarDesc",
                  "Contact our team of experts to find out how we can help your business grow.",
                )}
              </p>
              <div className="flex flex-col gap-2 font-bold text-xs mb-6 text-white/95 items-center">
                <span className="flex items-center gap-2">
                  <Phone className="w-4 h-4 text-white/80" />
                  +998 65 224 64 35
                </span>
                <span className="flex items-center gap-2">
                  <Phone className="w-4 h-4 text-white/80" />
                  +998 65 223 28 83
                </span>
                <span className="flex items-center gap-2">
                  <Mail className="w-4 h-4 text-white/80" />
                  info@bstu.uz
                </span>
              </div>
              <Link
                to="/#contact"
                className="bg-white text-primary hover:bg-gray-50 px-6 py-3 rounded-xl text-xs font-bold transition-colors shadow-sm"
              >
                {t("common.contactUs", "Get in Touch")}
              </Link>
            </div>
          </div>

          {/* Right Main Content */}
          <div className="lg:col-span-8 flex flex-col gap-8 lg:pl-8 rtl:lg:pl-0 rtl:lg:pr-8 text-start">
            {/* Large Image illustration */}
            <div className="rounded-3xl overflow-hidden shadow-lg aspect-video bg-gray-100 border border-gray-50">
              <img
                src="/assets/img/services.jpg"
                alt={t("common.serviceDetails")}
                className="w-full h-full object-cover"
              />
            </div>

            {/* Overview */}
            <div>
              <h3 className="text-2xl md:text-3xl font-bold text-navy mb-4">
                {t(`services.${service.id}.heading`, serviceName + " Overview")}
              </h3>
              <p className="text-gray-500 leading-relaxed mb-6 text-sm md:text-base">
                {t(
                  `services.${service.id}.detailedDesc`,
                  t(`services.${service.id}.desc`, service.description),
                )}
              </p>
              <div className="flex flex-col gap-3 mb-6">
                {[
                  "Aut eum totam accusantium voluptatem.",
                  "Assumenda et porro nisi nihil nesciunt voluptatibus.",
                  "Ullamco laboris nisi ut aliquip ex ea",
                ].map((benefit, bIdx) => (
                  <div key={bIdx} className="flex gap-3 items-center">
                    <CheckCircle className="w-5 h-5 text-green-500 shrink-0" />
                    <span className="text-sm font-bold text-navy">
                      {t(`services.${service.id}.benefits.${bIdx}`, benefit)}
                    </span>
                  </div>
                ))}
              </div>
              <p className="text-gray-500 leading-relaxed text-sm md:text-base mb-4">
                {t(
                  `services.${service.id}.additionalDesc1`,
                  "Est reprehenderit voluptatem necessitatibus asperiores neque sed ea illo. Deleniti quam sequi optio iste veniam repellat odit. Aut pariatur itaque nesciunt fuga.",
                )}
              </p>
              <p className="text-gray-500 leading-relaxed text-sm md:text-base">
                {t(
                  `services.${service.id}.additionalDesc2`,
                  "Sunt rem odit accusantium omnis perspiciatis officia. Laboriosam aut consequuntur recusandae mollitia doloremque est architecto cupiditate ullam. Quia est ut occaecati fuga.",
                )}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
