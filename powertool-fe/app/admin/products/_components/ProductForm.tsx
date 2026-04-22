"use client";

import { useEffect, useMemo, useState } from "react";
import { useRouter } from "next/navigation";
import {
  createProduct,
  deleteProductImage,
  getAttributesForSelect,
  getBrandsForSelect,
  getCategoriesForSelect,
  getProduct,
  getProductAttributes,
  setPrimaryProductImage,
  syncProductAttributes,
  updateProduct,
  uploadProductImage,
  type Product,
  type ProductAttribute,
  type ProductFormPayload,
  type ProductImage,
  type ProductOption,
} from "../../../../services/admin/product.service";
import { getApiErrorMessage } from "../../../../lib/api";

type ProductFormProps = {
  mode: "create" | "edit";
  productId?: string;
};

type PendingImage = {
  image: string;
  alt: string;
  is_primary: boolean;
  sort_order: number;
};

const defaultForm: ProductFormPayload = {
  brand_id: null,
  category_id: null,
  name: "",
  slug: "",
  sku: "",
  short_description: "",
  description: "",
  content: "",
  thumbnail: "",
  price: 0,
  sale_price: null,
  stock_quantity: 0,
  status: true,
  is_featured: false,
  is_new: false,
  meta_title: "",
  meta_description: "",
  meta_keywords: "",
};

function toBool(value: unknown) {
  return value === true || value === 1 || value === "1";
}

export default function ProductForm({ mode, productId }: ProductFormProps) {
  const router = useRouter();

  const [loading, setLoading] = useState(mode === "edit");
  const [saving, setSaving] = useState(false);
  const [errorText, setErrorText] = useState("");
  const [successText, setSuccessText] = useState("");

  const [form, setForm] = useState<ProductFormPayload>(defaultForm);
  const [brands, setBrands] = useState<ProductOption[]>([]);
  const [categories, setCategories] = useState<ProductOption[]>([]);
  const [attributes, setAttributes] = useState<ProductAttribute[]>([]);
  const [selectedAttributeIds, setSelectedAttributeIds] = useState<number[]>([]);
  const [existingImages, setExistingImages] = useState<ProductImage[]>([]);
  const [pendingImages, setPendingImages] = useState<PendingImage[]>([
    { image: "", alt: "", is_primary: false, sort_order: 0 },
  ]);

  useEffect(() => {
    let active = true;

    async function bootstrap() {
      try {
        const [brandRows, categoryRows, attributeRows] = await Promise.all([
          getBrandsForSelect(),
          getCategoriesForSelect(),
          getAttributesForSelect(),
        ]);

        if (!active) return;

        setBrands(brandRows);
        setCategories(categoryRows);
        setAttributes(attributeRows);

        if (mode === "edit" && productId) {
          const [product, productAttrs] = await Promise.all([
            getProduct(productId),
            getProductAttributes(productId),
          ]);

          if (!active) return;

          const detail = product as Product;

          setForm({
            brand_id: detail.brand_id ?? null,
            category_id: detail.category_id ?? null,
            name: detail.name ?? "",
            slug: detail.slug ?? "",
            sku: detail.sku ?? "",
            short_description: detail.short_description ?? "",
            description: detail.description ?? "",
            content: detail.content ?? "",
            thumbnail: detail.thumbnail ?? "",
            price: Number(detail.price ?? 0),
            sale_price:
              detail.sale_price === null || detail.sale_price === undefined || detail.sale_price === ""
                ? null
                : Number(detail.sale_price),
            stock_quantity: Number(detail.stock_quantity ?? 0),
            status: toBool(detail.status),
            is_featured: toBool(detail.is_featured),
            is_new: toBool(detail.is_new),
            meta_title: detail.meta_title ?? "",
            meta_description: detail.meta_description ?? "",
            meta_keywords: detail.meta_keywords ?? "",
          });

          setSelectedAttributeIds(productAttrs.map((item) => item.id));
          setExistingImages(Array.isArray(detail.images) ? detail.images : []);
        }
      } catch (error) {
        if (!active) return;
        setErrorText(getApiErrorMessage(error));
      } finally {
        if (active) setLoading(false);
      }
    }

    bootstrap();

    return () => {
      active = false;
    };
  }, [mode, productId]);

  const title = useMemo(() => {
    return mode === "create" ? "Tạo sản phẩm mới" : "Cập nhật sản phẩm";
  }, [mode]);

  function setField<K extends keyof ProductFormPayload>(key: K, value: ProductFormPayload[K]) {
    setForm((prev) => ({
      ...prev,
      [key]: value,
    }));
  }

  function toggleAttribute(attributeId: number) {
    setSelectedAttributeIds((prev) =>
      prev.includes(attributeId)
        ? prev.filter((id) => id !== attributeId)
        : [...prev, attributeId]
    );
  }

  function addPendingImageRow() {
    setPendingImages((prev) => [
      ...prev,
      { image: "", alt: "", is_primary: false, sort_order: prev.length },
    ]);
  }

  function updatePendingImage(index: number, field: keyof PendingImage, value: string | number | boolean) {
    setPendingImages((prev) =>
      prev.map((item, idx) =>
        idx === index ? { ...item, [field]: value } : item
      )
    );
  }

  function removePendingImageRow(index: number) {
    setPendingImages((prev) => prev.filter((_, idx) => idx !== index));
  }

  async function handleSave() {
    setErrorText("");
    setSuccessText("");

    if (!form.name.trim()) {
      setErrorText("Tên sản phẩm là bắt buộc.");
      return;
    }

    if (Number.isNaN(Number(form.price))) {
      setErrorText("Giá sản phẩm không hợp lệ.");
      return;
    }

    setSaving(true);

    try {
      const payload: ProductFormPayload = {
        ...form,
        price: Number(form.price || 0),
        sale_price:
          form.sale_price === null || form.sale_price === undefined || form.sale_price === ("" as never)
            ? null
            : Number(form.sale_price),
        stock_quantity: Number(form.stock_quantity || 0),
      };

      let savedProduct: Product;

      if (mode === "create") {
        savedProduct = await createProduct(payload);
      } else {
        savedProduct = await updateProduct(productId as string, payload);
      }

      const savedId = savedProduct.id || Number(productId);

      await syncProductAttributes(savedId, selectedAttributeIds);

      const validPendingImages = pendingImages.filter((item) => item.image.trim() !== "");

      if (validPendingImages.length > 0) {
        for (const item of validPendingImages) {
          await uploadProductImage(savedId, {
            image: item.image,
            alt: item.alt,
            is_primary: item.is_primary,
            sort_order: item.sort_order,
          });
        }
      }

      setSuccessText(
        mode === "create"
          ? "Tạo sản phẩm thành công."
          : "Cập nhật sản phẩm thành công."
      );

      if (mode === "create") {
        router.push(`/admin/products/${savedId}/edit`);
        return;
      }

      const refreshed = await getProduct(savedId);
      const refreshedAttrs = await getProductAttributes(savedId);

      setExistingImages(Array.isArray(refreshed.images) ? refreshed.images : []);
      setSelectedAttributeIds(refreshedAttrs.map((item) => item.id));
      setPendingImages([{ image: "", alt: "", is_primary: false, sort_order: 0 }]);
    } catch (error) {
      setErrorText(getApiErrorMessage(error));
    } finally {
      setSaving(false);
    }
  }

  async function handleDeleteImage(imageId: number) {
    const ok = window.confirm("Bạn có chắc muốn xóa ảnh này?");
    if (!ok) return;

    try {
      await deleteProductImage(imageId);
      setExistingImages((prev) => prev.filter((item) => item.id !== imageId));
    } catch (error) {
      setErrorText(getApiErrorMessage(error));
    }
  }

  async function handleSetPrimaryImage(imageId: number) {
    try {
      await setPrimaryProductImage(imageId);
      setExistingImages((prev) =>
        prev.map((item) => ({
          ...item,
          is_primary: item.id === imageId,
        }))
      );
    } catch (error) {
      setErrorText(getApiErrorMessage(error));
    }
  }

  if (loading) {
    return (
      <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        Đang tải dữ liệu sản phẩm...
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div>
            <h1 className="text-2xl font-bold text-slate-900">{title}</h1>
            <p className="mt-2 text-slate-600">
              Quản lý đầy đủ thông tin, thuộc tính và ảnh của sản phẩm.
            </p>
          </div>

          <div className="flex gap-3">
            <button
              type="button"
              onClick={() => router.push("/admin/products")}
              className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
            >
              Quay lại
            </button>
            <button
              type="button"
              onClick={handleSave}
              disabled={saving}
              className="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
            >
              {saving ? "Đang lưu..." : mode === "create" ? "Tạo sản phẩm" : "Lưu thay đổi"}
            </button>
          </div>
        </div>

        {errorText ? (
          <div className="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            {errorText}
          </div>
        ) : null}

        {successText ? (
          <div className="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {successText}
          </div>
        ) : null}
      </div>

      <div className="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
        <div className="space-y-6">
          <section className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 className="text-lg font-semibold text-slate-900">Thông tin cơ bản</h2>

            <div className="mt-5 grid gap-4 md:grid-cols-2">
              <div className="md:col-span-2">
                <label className="mb-2 block text-sm font-medium text-slate-800">
                  Tên sản phẩm
                </label>
                <input
                  value={form.name}
                  onChange={(e) => setField("name", e.target.value)}
                  className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
                  placeholder="Nhập tên sản phẩm"
                />
              </div>

              <div>
                <label className="mb-2 block text-sm font-medium text-slate-800">
                  Slug
                </label>
                <input
                  value={form.slug}
                  onChange={(e) => setField("slug", e.target.value)}
                  className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
                  placeholder="tu-dong-hoac-nhap-tay"
                />
              </div>

              <div>
                <label className="mb-2 block text-sm font-medium text-slate-800">
                  SKU
                </label>
                <input
                  value={form.sku}
                  onChange={(e) => setField("sku", e.target.value)}
                  className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
                  placeholder="Mã sản phẩm"
                />
              </div>

              <div>
                <label className="mb-2 block text-sm font-medium text-slate-800">
                  Thương hiệu
                </label>
                <select
                  value={form.brand_id ?? ""}
                  onChange={(e) =>
                    setField("brand_id", e.target.value ? Number(e.target.value) : null)
                  }
                  className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
                >
                  <option value="">-- Chọn thương hiệu --</option>
                  {brands.map((item) => (
                    <option key={item.id} value={item.id}>
                      {item.name}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="mb-2 block text-sm font-medium text-slate-800">
                  Danh mục
                </label>
                <select
                  value={form.category_id ?? ""}
                  onChange={(e) =>
                    setField("category_id", e.target.value ? Number(e.target.value) : null)
                  }
                  className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
                >
                  <option value="">-- Chọn danh mục --</option>
                  {categories.map((item) => (
                    <option key={item.id} value={item.id}>
                      {item.name}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="mb-2 block text-sm font-medium text-slate-800">
                  Giá gốc
                </label>
                <input
                  type="number"
                  value={form.price}
                  onChange={(e) => setField("price", Number(e.target.value))}
                  className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
                />
              </div>

              <div>
                <label className="mb-2 block text-sm font-medium text-slate-800">
                  Giá khuyến mãi
                </label>
                <input
                  type="number"
                  value={form.sale_price ?? ""}
                  onChange={(e) =>
                    setField("sale_price", e.target.value === "" ? null : Number(e.target.value))
                  }
                  className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
                />
              </div>

              <div>
                <label className="mb-2 block text-sm font-medium text-slate-800">
                  Tồn kho
                </label>
                <input
                  type="number"
                  value={form.stock_quantity}
                  onChange={(e) => setField("stock_quantity", Number(e.target.value))}
                  className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
                />
              </div>

              <div className="md:col-span-2">
                <label className="mb-2 block text-sm font-medium text-slate-800">
                  Thumbnail
                </label>
                <input
                  value={form.thumbnail}
                  onChange={(e) => setField("thumbnail", e.target.value)}
                  className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
                  placeholder="URL hoặc path ảnh đại diện"
                />
              </div>

              <div className="md:col-span-2">
                <label className="mb-2 block text-sm font-medium text-slate-800">
                  Mô tả ngắn
                </label>
                <textarea
                  value={form.short_description}
                  onChange={(e) => setField("short_description", e.target.value)}
                  rows={4}
                  className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
                />
              </div>

              <div className="md:col-span-2">
                <label className="mb-2 block text-sm font-medium text-slate-800">
                  Mô tả chi tiết
                </label>
                <textarea
                  value={form.description}
                  onChange={(e) => setField("description", e.target.value)}
                  rows={6}
                  className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
                />
              </div>

              <div className="md:col-span-2">
                <label className="mb-2 block text-sm font-medium text-slate-800">
                  Nội dung bổ sung
                </label>
                <textarea
                  value={form.content}
                  onChange={(e) => setField("content", e.target.value)}
                  rows={6}
                  className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
                />
              </div>
            </div>
          </section>

          <section className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 className="text-lg font-semibold text-slate-900">SEO</h2>

            <div className="mt-5 grid gap-4">
              <div>
                <label className="mb-2 block text-sm font-medium text-slate-800">
                  Meta title
                </label>
                <input
                  value={form.meta_title}
                  onChange={(e) => setField("meta_title", e.target.value)}
                  className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
                />
              </div>

              <div>
                <label className="mb-2 block text-sm font-medium text-slate-800">
                  Meta description
                </label>
                <textarea
                  value={form.meta_description}
                  onChange={(e) => setField("meta_description", e.target.value)}
                  rows={4}
                  className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
                />
              </div>

              <div>
                <label className="mb-2 block text-sm font-medium text-slate-800">
                  Meta keywords
                </label>
                <input
                  value={form.meta_keywords}
                  onChange={(e) => setField("meta_keywords", e.target.value)}
                  className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
                />
              </div>
            </div>
          </section>
        </div>

        <div className="space-y-6">
          <section className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 className="text-lg font-semibold text-slate-900">Trạng thái</h2>

            <div className="mt-5 space-y-4">
              <label className="flex items-center gap-3">
                <input
                  type="checkbox"
                  checked={form.status}
                  onChange={(e) => setField("status", e.target.checked)}
                />
                <span className="text-sm text-slate-700">Hiển thị sản phẩm</span>
              </label>

              <label className="flex items-center gap-3">
                <input
                  type="checkbox"
                  checked={form.is_featured}
                  onChange={(e) => setField("is_featured", e.target.checked)}
                />
                <span className="text-sm text-slate-700">Sản phẩm nổi bật</span>
              </label>

              <label className="flex items-center gap-3">
                <input
                  type="checkbox"
                  checked={form.is_new}
                  onChange={(e) => setField("is_new", e.target.checked)}
                />
                <span className="text-sm text-slate-700">Sản phẩm mới</span>
              </label>
            </div>
          </section>

          <section className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 className="text-lg font-semibold text-slate-900">Thuộc tính sản phẩm</h2>

            <div className="mt-5 max-h-80 space-y-3 overflow-auto">
              {attributes.map((item) => (
                <label key={item.id} className="flex items-center gap-3">
                  <input
                    type="checkbox"
                    checked={selectedAttributeIds.includes(item.id)}
                    onChange={() => toggleAttribute(item.id)}
                  />
                  <span className="text-sm text-slate-700">{item.name}</span>
                </label>
              ))}
            </div>
          </section>

          <section className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 className="text-lg font-semibold text-slate-900">Ảnh sản phẩm</h2>

            {mode === "edit" && existingImages.length > 0 ? (
              <div className="mt-5 space-y-4">
                {existingImages.map((image) => (
                  <div
                    key={image.id}
                    className="rounded-xl border border-slate-200 p-4"
                  >
                    <p className="break-all text-sm font-medium text-slate-800">
                      {image.image}
                    </p>
                    <p className="mt-1 text-xs text-slate-500">
                      {image.alt || "Không có alt"}
                    </p>

                    <div className="mt-3 flex flex-wrap gap-2">
                      {image.is_primary ? (
                        <span className="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                          Ảnh chính
                        </span>
                      ) : (
                        <button
                          type="button"
                          onClick={() => handleSetPrimaryImage(image.id)}
                          className="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700"
                        >
                          Đặt làm ảnh chính
                        </button>
                      )}

                      <button
                        type="button"
                        onClick={() => handleDeleteImage(image.id)}
                        className="rounded-lg border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-700"
                      >
                        Xóa ảnh
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            ) : null}

            <div className="mt-5 space-y-4">
              {pendingImages.map((item, index) => (
                <div key={index} className="rounded-xl border border-slate-200 p-4">
                  <div className="space-y-3">
                    <input
                      value={item.image}
                      onChange={(e) => updatePendingImage(index, "image", e.target.value)}
                      className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
                      placeholder="URL / path ảnh"
                    />

                    <input
                      value={item.alt}
                      onChange={(e) => updatePendingImage(index, "alt", e.target.value)}
                      className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
                      placeholder="Alt text"
                    />

                    <div className="grid gap-3 md:grid-cols-2">
                      <input
                        type="number"
                        value={item.sort_order}
                        onChange={(e) => updatePendingImage(index, "sort_order", Number(e.target.value))}
                        className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
                        placeholder="Sort order"
                      />

                      <label className="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3">
                        <input
                          type="checkbox"
                          checked={item.is_primary}
                          onChange={(e) => updatePendingImage(index, "is_primary", e.target.checked)}
                        />
                        <span className="text-sm text-slate-700">Ảnh chính</span>
                      </label>
                    </div>
                  </div>

                  <div className="mt-3">
                    <button
                      type="button"
                      onClick={() => removePendingImageRow(index)}
                      className="rounded-lg border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-700"
                    >
                      Xóa dòng ảnh
                    </button>
                  </div>
                </div>
              ))}

              <button
                type="button"
                onClick={addPendingImageRow}
                className="w-full rounded-xl border border-dashed border-slate-300 px-4 py-3 text-sm font-medium text-slate-700"
              >
                + Thêm ảnh
              </button>

              {mode === "create" ? (
                <p className="text-xs leading-6 text-slate-500">
                  Ảnh sẽ được upload sau khi tạo sản phẩm thành công.
                </p>
              ) : null}
            </div>
          </section>
        </div>
      </div>
    </div>
  );
}