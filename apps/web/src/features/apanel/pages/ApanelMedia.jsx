import React, { useState, useEffect } from "react";
import { apanelService } from "../../../services/apanelService";
import {
  Image as ImageIcon,
  Upload,
  Search,
  Link2,
  Trash2,
  Loader2,
  ClipboardCheck,
} from "lucide-react";
import ConfirmDialog from "../components/ConfirmDialog";
import Pagination from "../components/Pagination";
import { publicAssetUrl } from "../../../lib/api";

function mediaUrl(path) {
  return publicAssetUrl(path);
}

export default function ApanelMedia() {
  const [mediaList, setMediaList] = useState([]);
  const [loading, setLoading] = useState(true);
  const [uploading, setUploading] = useState(false);
  const [search, setSearch] = useState("");
  const [uploadMeta, setUploadMeta] = useState({
    title: "",
    alt_text: "",
    type: "image",
    is_public: true,
  });
  const [copiedId, setCopiedId] = useState(null);
  const [page, setPage] = useState(1);
  const [total, setTotal] = useState(0);
  const [lastPage, setLastPage] = useState(1);

  // Delete dialog states
  const [deleteTarget, setDeleteTarget] = useState(null);

  const fetchMedia = React.useCallback(async () => {
    try {
      setLoading(true);
      const pageData = await apanelService.listPage("media", {
        search,
        page,
        per_page: 16,
      });
      setMediaList(pageData.items);
      setTotal(pageData.total);
      setLastPage(pageData.lastPage);
    } catch {
      console.warn("Failed to load media assets");
    } finally {
      setLoading(false);
    }
  }, [search, page]);

  useEffect(() => {
    fetchMedia();
  }, [fetchMedia]);

  const handleFileUpload = async (e) => {
    const file = e.target.files?.[0];
    if (!file) return;

    try {
      setUploading(true);
      await apanelService.uploadMedia(file, {
        ...uploadMeta,
        alt_key: uploadMeta.alt_text || file.name,
        title: uploadMeta.title || file.name,
        is_public: uploadMeta.is_public ? "1" : "0",
      });
      fetchMedia();
    } catch {
      alert("Failed to upload media file");
    } finally {
      setUploading(false);
    }
  };

  const handleCopyPath = (media) => {
    const mediaPath = String(media.path || "");
    navigator.clipboard.writeText(mediaPath);
    setCopiedId(media.id);
    setTimeout(() => setCopiedId(null), 2000);
  };

  const handleDeleteConfirm = async () => {
    if (!deleteTarget) return;

    try {
      await apanelService.delete("media", deleteTarget.id);
      setDeleteTarget(null);
      fetchMedia();
    } catch {
      alert("Failed to delete media asset");
    }
  };

  return (
    <div className="space-y-6 animate-in fade-in duration-200">
      {/* Header */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-navy uppercase tracking-wider">
            Media Library
          </h1>
          <p className="text-gray-400 text-xs font-semibold mt-1">
            Upload and manage campus documents, pictures, and video assets
          </p>
        </div>

        <label className="bg-primary hover:bg-primary-hover text-white px-5 py-2.5 rounded-xl text-xs font-extrabold shadow-sm hover:shadow-md transition-all cursor-pointer inline-flex items-center gap-1.5 self-stretch sm:self-auto justify-center">
          {uploading ? (
            <Loader2 className="w-4 h-4 animate-spin" />
          ) : (
            <Upload className="w-4 h-4" />
          )}
          Upload New File
          <input
            type="file"
            accept="image/*,video/*,application/pdf"
            onChange={handleFileUpload}
            disabled={uploading}
            className="hidden"
          />
        </label>
      </div>

      {/* Toolbar Search */}
      <div className="bg-white border border-gray-100 p-5 rounded-3xl shadow-xs">
        <div className="grid grid-cols-1 lg:grid-cols-5 gap-3">
          <div className="relative lg:col-span-2">
          <input
            type="text"
            placeholder="Search filenames..."
            value={search}
            onChange={(e) => {
              setSearch(e.target.value);
              setPage(1);
            }}
            className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold bg-white text-navy"
          />
          <Search className="absolute left-3.5 top-3.5 w-3.5 h-3.5 text-gray-400" />
          </div>
          <input
            type="text"
            placeholder="Upload title"
            value={uploadMeta.title}
            onChange={(e) =>
              setUploadMeta((prev) => ({ ...prev, title: e.target.value }))
            }
            className="px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold bg-white text-navy"
          />
          <input
            type="text"
            placeholder="Alt text"
            value={uploadMeta.alt_text}
            onChange={(e) =>
              setUploadMeta((prev) => ({ ...prev, alt_text: e.target.value }))
            }
            className="px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold bg-white text-navy"
          />
          <div className="flex items-center gap-3">
            <select
              value={uploadMeta.type}
              onChange={(e) =>
                setUploadMeta((prev) => ({ ...prev, type: e.target.value }))
              }
              className="grow px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold bg-white text-navy"
            >
              <option value="image">Image</option>
              <option value="document">Document</option>
              <option value="video">Video</option>
            </select>
            <label className="inline-flex items-center gap-1.5 text-xs font-bold text-navy">
              <input
                type="checkbox"
                checked={uploadMeta.is_public}
                onChange={(e) =>
                  setUploadMeta((prev) => ({
                    ...prev,
                    is_public: e.target.checked,
                  }))
                }
              />
              Public
            </label>
          </div>
        </div>
      </div>

      {/* Grid List */}
      {loading ? (
        <div className="flex items-center justify-center min-h-75">
          <Loader2 className="w-8 h-8 animate-spin text-primary" />
        </div>
      ) : mediaList.length === 0 ? (
        <div className="bg-white border border-gray-100 rounded-3xl p-12 text-center text-gray-400 font-semibold shadow-xs">
          <div className="flex flex-col items-center gap-3">
            <ImageIcon className="w-12 h-12 text-gray-300 stroke-1" />
            <p className="text-sm">No media assets found</p>
          </div>
        </div>
      ) : (
        <div className="space-y-6">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
            {mediaList.map((media) => {
              const mediaPath = String(
                media.path || media.url || media.file_path || ""
              );
              const fileName = String(
                media.filename || media.title || mediaPath || ""
              );
              const mimeType = String(media.mime_type || media.mime || "");
              const fullUrl = mediaUrl(mediaPath);
              const isImg =
                /\.(jpeg|jpg|gif|png|webp|avif)$/i.test(
                  mediaPath || fileName
                ) || mimeType.startsWith("image/");
              const isCopied = copiedId === media.id;

              return (
                <div
                  key={media.id}
                  className="bg-white border border-gray-100 rounded-3xl overflow-hidden shadow-xs flex flex-col group hover:shadow-md transition-all"
                >
                  {/* Preview Container */}
                  <div className="h-40 bg-gray-50 flex items-center justify-center relative overflow-hidden shrink-0 border-b border-gray-50">
                    {isImg ? (
                      <img
                        src={fullUrl}
                        alt={media.alt_text || media.filename || media.title}
                        className="object-cover w-full h-full group-hover:scale-105 transition-all"
                      />
                    ) : (
                      <div className="text-xs font-extrabold text-gray-400 uppercase p-4 text-center break-all">
                        {String(media.filename || mediaPath || "asset")
                          .split(".")
                          .pop()}{" "}
                        file
                      </div>
                    )}
                  </div>

                  {/* Details */}
                  <div className="p-4 grow flex flex-col justify-between space-y-3">
                    <div className="space-y-1">
                      <p
                        className="text-xs font-extrabold text-navy truncate"
                        title={media.filename}
                      >
                        {media.filename || mediaPath || "Untitled asset"}
                      </p>
                      {media.title && (
                        <p className="text-[10px] text-gray-500 font-semibold truncate">
                          {media.title}
                        </p>
                      )}
                      <p className="text-[10px] text-gray-400 font-semibold">
                        Size: {Math.round((Number(media.size) || 0) / 1024)} KB
                      </p>
                    </div>

                    <div className="flex items-center gap-1">
                      <button
                        onClick={() => handleCopyPath(media)}
                        className={`grow inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-[10px] font-extrabold transition-all border cursor-pointer ${
                          isCopied
                            ? "bg-emerald-50 text-emerald-700 border-emerald-100"
                            : "bg-gray-50 text-navy border-gray-100 hover:bg-gray-100"
                        }`}
                      >
                        {isCopied ? (
                          <>
                            <ClipboardCheck className="w-3.5 h-3.5" />
                            Copied
                          </>
                        ) : (
                          <>
                            <Link2 className="w-3.5 h-3.5" />
                            Copy Path
                          </>
                        )}
                      </button>

                      <button
                        onClick={() => setDeleteTarget(media)}
                        className="p-2 border border-gray-100 hover:border-red-100 hover:bg-red-50 text-gray-400 hover:text-red-600 rounded-xl transition-all cursor-pointer shrink-0"
                        title="Delete File"
                      >
                        <Trash2 className="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>

          <Pagination
            currentPage={page}
            lastPage={lastPage}
            total={total}
            perPage={16}
            onPageChange={setPage}
          />
        </div>
      )}

      {/* Confirm Deletion Dialog */}
      <ConfirmDialog
        isOpen={!!deleteTarget}
        title="Delete Media File?"
        message={`Are you sure you want to delete ${deleteTarget?.filename}? This will permanently remove it from public storage.`}
        onConfirm={handleDeleteConfirm}
        onCancel={() => setDeleteTarget(null)}
      />
    </div>
  );
}
