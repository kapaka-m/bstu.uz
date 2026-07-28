import React, { useState, useEffect } from "react";
import { Image, Upload, Search, X, Loader2, Link2 } from "lucide-react";
import { apanelService } from "../../../services/apanelService";
import { publicAssetUrl } from "../../../lib/api";

export default function MediaPicker({ value, onChange, label }) {
  const [isOpen, setIsOpen] = useState(false);
  const [mediaList, setMediaList] = useState([]);
  const [loading, setLoading] = useState(false);
  const [uploading, setUploading] = useState(false);
  const [search, setSearch] = useState("");
  const [uploadError, setUploadError] = useState("");

  const fetchMedia = React.useCallback(async () => {
    try {
      setLoading(true);
      const pageData = await apanelService.listPage("media", { search });
      setMediaList(pageData.items);
    } catch (err) {
      console.error("Failed to load media list", err);
    } finally {
      setLoading(false);
    }
  }, [search]);

  useEffect(() => {
    if (isOpen) {
      fetchMedia();
    }
  }, [isOpen, fetchMedia]);

  const handleFileUpload = async (e) => {
    const file = e.target.files?.[0];
    if (!file) return;

    try {
      setUploading(true);
      setUploadError("");
      const res = await apanelService.uploadMedia(file, file.name);
      // Backend returns created media object
      if (res && res.path) {
        onChange(res.path);
        setIsOpen(false);
      } else {
        throw new Error("Invalid response format");
      }
    } catch (err) {
      setUploadError(err?.message || "Failed to upload file");
    } finally {
      setUploading(false);
    }
  };

  const handleSelectPath = (path) => {
    onChange(String(path || ""));
    setIsOpen(false);
  };

  return (
    <div className="w-full">
      {label && (
        <label className="block text-xs font-bold text-navy mb-1.5 uppercase tracking-wider">
          {label}
        </label>
      )}
      <div className="flex gap-2">
        <div className="relative grow">
          <input
            type="text"
            value={value || ""}
            onChange={(e) => onChange(e.target.value)}
            placeholder="cms/media-library/example.jpg"
            className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold bg-white text-navy"
          />
          <Link2 className="absolute left-3.5 top-3.5 w-3.5 h-3.5 text-gray-400" />
        </div>
        <button
          type="button"
          onClick={() => setIsOpen(true)}
          className="bg-navy hover:bg-navy-dark text-white px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer inline-flex items-center gap-1.5 shrink-0"
        >
          <Image className="w-4 h-4" />
          Browse
        </button>
      </div>

      {/* Modal Browse overlay */}
      {isOpen && (
        <div className="fixed inset-0 z-9999 flex items-center justify-center bg-navy/40 backdrop-blur-xs p-4">
          <div className="bg-white border border-gray-100 rounded-3xl max-w-2xl w-full p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-200 flex flex-col max-h-[85vh]">
            {/* Header */}
            <div className="flex justify-between items-center pb-4 border-b border-gray-100 mb-4">
              <h3 className="font-extrabold text-navy text-base">
                Select Media Asset
              </h3>
              <button
                type="button"
                onClick={() => setIsOpen(false)}
                className="text-gray-400 hover:text-navy cursor-pointer"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Upload & Search controls */}
            <div className="flex flex-col sm:flex-row gap-3 mb-4">
              <div className="relative grow">
                <input
                  type="text"
                  placeholder="Search files..."
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold bg-white text-navy"
                />
                <Search className="absolute left-3.5 top-3.5 w-3.5 h-3.5 text-gray-400" />
              </div>

              <label className="bg-primary hover:bg-primary-hover text-white px-4 py-2.5 rounded-xl text-xs font-extrabold transition-all cursor-pointer inline-flex items-center justify-center gap-1.5 shrink-0">
                {uploading ? (
                  <Loader2 className="w-4 h-4 animate-spin" />
                ) : (
                  <Upload className="w-4 h-4" />
                )}
                Upload Asset
                <input
                  type="file"
                  accept="image/*,video/*,application/pdf"
                  onChange={handleFileUpload}
                  disabled={uploading}
                  className="hidden"
                />
              </label>
            </div>

            {uploadError && (
              <div className="mb-4 text-xs font-bold text-rose-600 bg-rose-50 border border-rose-100 p-3 rounded-xl">
                {uploadError}
              </div>
            )}

            {/* Grid display */}
            <div className="grow overflow-y-auto min-h-62.5 border border-dashed border-gray-200 rounded-2xl p-4 bg-gray-50/50">
              {loading ? (
                <div className="flex items-center justify-center h-full">
                  <Loader2 className="w-8 h-8 animate-spin text-primary" />
                </div>
              ) : mediaList.length === 0 ? (
                <div className="flex flex-col items-center justify-center h-full text-gray-400 gap-2">
                  <Image className="w-10 h-10 stroke-1" />
                  <p className="text-xs font-semibold">No media files found</p>
                </div>
              ) : (
                <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
                  {mediaList.map((media) => {
                    const mediaPath = String(media.path || "");
                    const fullUrl = publicAssetUrl(mediaPath);
                    const isImg = /\.(jpeg|jpg|gif|png|webp)$/i.test(
                      mediaPath,
                    );

                    return (
                      <div
                        key={media.id}
                        onClick={() => handleSelectPath(mediaPath)}
                        className="group border border-gray-200 bg-white rounded-xl overflow-hidden hover:border-primary hover:shadow-md cursor-pointer transition-all flex flex-col"
                      >
                        <div className="h-24 bg-gray-100 flex items-center justify-center relative overflow-hidden shrink-0">
                          {isImg ? (
                            <img
                              src={fullUrl}
                              alt={media.alt_text || media.filename || media.title}
                              className="object-cover w-full h-full group-hover:scale-105 transition-all"
                            />
                          ) : (
                            <div className="text-xs font-extrabold text-gray-400 uppercase p-2 text-center break-all">
                              {String(media.filename || mediaPath || "asset")
                                .split(".")
                                .pop()}{" "}
                              file
                            </div>
                          )}
                        </div>
                        <div className="p-2 border-t border-gray-100 grow flex flex-col justify-between">
                          <p
                            className="text-[10px] font-bold text-navy truncate"
                            title={media.filename}
                          >
                            {media.filename || mediaPath || "Untitled asset"}
                          </p>
                          <p className="text-[9px] text-gray-400 font-semibold">
                            {Math.round((Number(media.size) || 0) / 1024)} KB
                          </p>
                        </div>
                      </div>
                    );
                  })}
                </div>
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
