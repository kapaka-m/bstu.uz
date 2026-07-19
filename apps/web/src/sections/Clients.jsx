import React from "react";
import { Swiper, SwiperSlide } from "swiper/react";
import { Pagination, Autoplay } from "swiper/modules";
import { motion } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";

import "swiper/css";
import "swiper/css/pagination";

export default function Clients() {
  const { t } = useLanguage();
  const clientLogos = [
    "/assets/img/clients/client-1.png",
    "/assets/img/clients/client-2.png",
    "/assets/img/clients/client-3.png",
    "/assets/img/clients/client-4.png",
    "/assets/img/clients/client-5.png",
    "/assets/img/clients/client-6.png",
    "/assets/img/clients/client-7.png",
    "/assets/img/clients/client-8.png",
  ];

  return (
    <section id="clients" className="py-16 bg-white border-t border-gray-50 overflow-hidden">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl">
        <div className="text-center max-w-2xl mx-auto mb-10">
          <h2 className="text-sm font-extrabold uppercase tracking-widest text-primary mb-2">{t("home.clients.tag")}</h2>
          <p className="text-gray-500 text-sm font-semibold">{t("home.clients.title")}</p>
        </div>

        <motion.div
          initial={{ opacity: 0 }}
          whileInView={{ opacity: 1 }}
          viewport={{ once: true }}
          transition={{ duration: 0.8 }}
        >
          <Swiper
            modules={[Pagination, Autoplay]}
            spaceBetween={24}
            slidesPerView={2}
            pagination={{ clickable: true }}
            autoplay={{ delay: 2800, disableOnInteraction: false }}
            breakpoints={{
              480: { slidesPerView: 3 },
              768: { slidesPerView: 4 },
              1024: { slidesPerView: 6 },
            }}
            className="pb-12"
          >
            {clientLogos.map((logo, index) => (
              <SwiperSlide key={index} className="flex items-center justify-center">
                <div className="h-20 w-full rounded-xl border border-gray-100 bg-white shadow-sm flex items-center justify-center p-4">
                  <img
                    src={logo}
                    alt={`${t("home.clients.partnerAlt")} ${index + 1}`}
                    className="max-h-10 w-auto object-contain opacity-80 hover:opacity-100 transition-opacity"
                    loading="lazy"
                  />
                </div>
              </SwiperSlide>
            ))}
          </Swiper>
        </motion.div>
      </div>
    </section>
  );
}
