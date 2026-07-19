import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { ArrowRight, Calendar, User } from "lucide-react";
import { motion } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";
import { blogService } from "../services/blogService";

export default function RecentBlog() {
  const { language } = useLanguage();
  const [settings, setSettings] = useState({});
  const [recentPosts, setRecentPosts] = useState([]);

  useEffect(() => {
    let alive = true;

    const loadBlog = async () => {
      try {
        const nextSettings = await blogService.getSettings();
        const response = await blogService.getBlog({
          per_page: Number(nextSettings.home_limit || 3),
          page: 1,
        });

        if (alive) {
          setSettings(nextSettings);
          setRecentPosts(response.items || []);
        }
      } catch (err) {
        console.error("Failed to load recent blog posts", err);
      }
    };

    loadBlog();

    return () => {
      alive = false;
    };
  }, [language]);

  if (recentPosts.length === 0) {
    return null;
  }

  return (
    <section
      id="recent-blog"
      className="py-24 bg-primary-light border-t border-gray-100"
    >
      <div className="container mx-auto px-4 md:px-8 max-w-7xl">
        {/* Section Header */}
        <div className="text-center max-w-2xl mx-auto mb-16">
          <h2 className="text-sm font-extrabold uppercase tracking-widest text-primary mb-3">
            {settings.home_tag || ""}
          </h2>
          <p className="text-3xl md:text-4xl font-extrabold text-navy">
            {settings.home_title || ""}
          </p>
        </div>

        {/* Blog Grid */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {recentPosts.map((post, index) => (
            <motion.div
              key={post.slug || post.id}
              initial={{ opacity: 0, y: 30 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ duration: 0.5, delay: index * 0.1 }}
              className="bg-white border border-gray-100/50 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1.5 flex flex-col group"
            >
              {/* Image */}
              <div className="aspect-16/10 overflow-hidden bg-gray-50 relative">
                <img
                  src={post.image}
                  alt={post.title}
                  className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                />
                <span className="absolute top-4 left-4 bg-primary text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">
                  {post.categoryLabel || post.category}
                </span>
              </div>

              {/* Card Body */}
              <div className="p-6 md:p-8 flex flex-col grow">
                {/* Meta */}
                <div className="flex flex-wrap items-center gap-4 text-xs font-semibold text-gray-400 mb-4">
                  <span className="flex items-center gap-1">
                    <Calendar className="w-3.5 h-3.5" />
                    {post.date}
                  </span>
                  <span className="flex items-center gap-1">
                    <User className="w-3.5 h-3.5" />
                    {post.author}
                  </span>
                </div>

                {/* Title */}
                <h3 className="text-lg md:text-xl font-bold text-navy group-hover:text-primary transition-colors duration-300 line-clamp-2 mb-3 leading-snug">
                  <Link to={`/blog/${post.slug}`}>{post.title}</Link>
                </h3>

                {/* Excerpt */}
                <p className="text-gray-500 text-sm leading-relaxed mb-6 line-clamp-3">
                  {post.excerpt}
                </p>

                {/* Read More */}
                <div className="mt-auto">
                  <Link
                    to={`/blog/${post.slug}`}
                    className="inline-flex items-center gap-1.5 text-sm font-bold text-navy hover:text-primary transition-colors"
                  >
                    {settings.read_more_label || ""}
                    <ArrowRight
                      className={`w-4 h-4 transition-transform duration-300 ${language === "ar" ? "rotate-180 group-hover:-translate-x-1" : "group-hover:translate-x-1"}`}
                    />
                  </Link>
                </div>
              </div>
            </motion.div>
          ))}
        </div>

        <div className="text-center mt-12">
          <Link
            to="/blog"
            className="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white px-6 py-3.5 rounded-xl text-sm font-semibold transition-all duration-300 shadow-md shadow-primary/20 hover:shadow-primary/30 hover:-translate-y-0.5"
          >
            {settings.view_all_label || ""}
            <ArrowRight
              className={`w-4 h-4 ${language === "ar" ? "rotate-180" : ""}`}
            />
          </Link>
        </div>
      </div>
    </section>
  );
}
