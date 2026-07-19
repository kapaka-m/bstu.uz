import React from "react";
import { Swiper, SwiperSlide } from "swiper/react";
import { Pagination, Autoplay } from "swiper/modules";
import { Star } from "lucide-react";
import { motion } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";

import "swiper/css";
import "swiper/css/pagination";

export default function Testimonials() {
  const { t } = useLanguage();
  const testimonials = t("home.testimonials.items", []);

  return (
    <section id="testimonials" className="py-24 bg-white border-t border-gray-50 overflow-hidden">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl">
        <div className="text-center max-w-2xl mx-auto mb-16">
          <h2 className="text-sm font-extrabold uppercase tracking-widest text-primary mb-3">{t("home.testimonials.tag")}</h2>
          <p className="text-3xl md:text-4xl font-extrabold text-navy">{t("home.testimonials.title")}</p>
        </div>

        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.7 }}
        >
          <Swiper
            modules={[Pagination, Autoplay]}
            spaceBetween={24}
            slidesPerView={1}
            pagination={{ clickable: true }}
            autoplay={{ delay: 4200, disableOnInteraction: false }}
            breakpoints={{
              768: { slidesPerView: 2 },
              1024: { slidesPerView: 3 },
            }}
            className="pb-16"
          >
            {testimonials.map((item) => (
              <SwiperSlide key={item.id} className="h-auto">
                <article className="bg-primary-light border border-gray-100 p-8 rounded-3xl h-full flex flex-col justify-between hover:shadow-lg transition-shadow duration-300">
                  <div>
                    <div className="flex items-center gap-1 mb-5 text-yellow-400">
                      <Star className="w-4 h-4 fill-current" />
                      <Star className="w-4 h-4 fill-current" />
                      <Star className="w-4 h-4 fill-current" />
                      <Star className="w-4 h-4 fill-current" />
                      <Star className="w-4 h-4 fill-current" />
                    </div>
                    <p className="text-gray-600 text-sm leading-relaxed">"{item.quote}"</p>
                  </div>

                  <div className="mt-6 border-t border-gray-200/60 pt-5">
                    <h3 className="font-extrabold text-navy text-sm">{item.name}</h3>
                    <p className="text-xs font-semibold text-gray-500 mt-1">{item.role}</p>
                  </div>
                </article>
              </SwiperSlide>
            ))}
          </Swiper>
        </motion.div>
      </div>
    </section>
  );
}


