import { api } from "../../lib/api";

export type ProductOption = {
  id: number;
  name: string;
};

export type ProductAttribute = {
  id: number;
  name: string;
  slug?: string;
};

export type ProductImage = {
  id: number;
  image: string;
  alt?: string | null;
  is_primary?: boolean | number;
  sort_order?: number | null;
};

export type Product = {
  id: number;
  brand_id?: number | null;
  category_id?: number | null;
  name: string;
  slug?: string | null;
  sku?: string | null;
  short_description?: string | null;
  description?: string | null;
  content?: string | null;
  thumbnail?: string | null;
  price?: number | string | null;
  sale_price?: number | string | null;
  stock_quantity?: number | null;
  status?: boolean | number;
  is_featured?: boolean | number;
  is_new?: boolean | number;
  meta_title?: string | null;
  meta_description?: string | null;
  meta_keywords?: string | null;
  brand?: ProductOption | null;
  category?: ProductOption | null;
  attributes?: ProductAttribute[];
  images?: ProductImage[];
  created_at?: string;
  updated_at?: string;
};

export type ProductFormPayload = {
  brand_id: number | null;
  category_id: number | null;
  name: string;
  slug: string;
  sku: string;
  short_description: string;
  description: string;
  content: string;
  thumbnail: string;
  price: number;
  sale_price: number | null;
  stock_quantity: number;
  status: boolean;
  is_featured: boolean;
  is_new: boolean;
  meta_title: string;
  meta_description: string;
  meta_keywords: string;
};

export type ProductListParams = {
  page?: number;
  per_page?: number;
  q?: string;
  brand_id?: string | number;
  category_id?: string | number;
  status?: string;
  is_featured?: string;
  is_new?: string;
};

export type PaginatedResult<T> = {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
};

type ApiEnvelope<T> = {
  success?: boolean;
  message?: string;
  data?: T;
};

function normalizePaginated<T>(payload: ApiEnvelope<PaginatedResult<T>> | PaginatedResult<T> | T[]): PaginatedResult<T> {
  const maybeWrapped = payload as ApiEnvelope<PaginatedResult<T>>;
  const data = maybeWrapped?.data ?? payload;

  if (Array.isArray(data)) {
    return {
      data,
      current_page: 1,
      last_page: 1,
      per_page: data.length,
      total: data.length,
    };
  }

  return {
    data: Array.isArray((data as PaginatedResult<T>)?.data) ? (data as PaginatedResult<T>).data : [],
    current_page: Number((data as PaginatedResult<T>)?.current_page ?? 1),
    last_page: Number((data as PaginatedResult<T>)?.last_page ?? 1),
    per_page: Number((data as PaginatedResult<T>)?.per_page ?? 10),
    total: Number((data as PaginatedResult<T>)?.total ?? 0),
  };
}

function normalizeItem<T>(payload: ApiEnvelope<T> | T): T {
  const maybeWrapped = payload as ApiEnvelope<T>;
  return (maybeWrapped?.data ?? payload) as T;
}

export async function getProducts(params: ProductListParams = {}) {
  const response = await api.get("/api/v1/admin/products", { params });
  return normalizePaginated<Product>(response.data);
}

export async function getProduct(id: string | number) {
  const response = await api.get(`/api/v1/admin/products/${id}`);
  return normalizeItem<Product>(response.data);
}

export async function createProduct(payload: ProductFormPayload) {
  const response = await api.post("/api/v1/admin/products", payload);
  return normalizeItem<Product>(response.data);
}

export async function updateProduct(id: string | number, payload: ProductFormPayload) {
  const response = await api.put(`/api/v1/admin/products/${id}`, payload);
  return normalizeItem<Product>(response.data);
}

export async function deleteProduct(id: string | number) {
  const response = await api.delete(`/api/v1/admin/products/${id}`);
  return response.data;
}

export async function getBrandsForSelect() {
  const response = await api.get("/api/v1/admin/brands", {
    params: { per_page: 1000 },
  });

  const result = normalizePaginated<ProductOption>(response.data);
  return result.data;
}

export async function getCategoriesForSelect() {
  const response = await api.get("/api/v1/admin/categories", {
    params: { per_page: 1000 },
  });

  const result = normalizePaginated<ProductOption>(response.data);
  return result.data;
}

export async function getAttributesForSelect() {
  const response = await api.get("/api/v1/admin/attributes", {
    params: { per_page: 1000 },
  });

  const result = normalizePaginated<ProductAttribute>(response.data);
  return result.data;
}

export async function getProductAttributes(productId: string | number) {
  const response = await api.get(`/api/v1/admin/products/${productId}/attributes`);
  const result = normalizeItem<ProductAttribute[] | { data?: ProductAttribute[] }>(response.data);

  if (Array.isArray(result)) return result;
  return Array.isArray(result?.data) ? result.data : [];
}

export async function syncProductAttributes(productId: string | number, attributeIds: number[]) {
  const response = await api.put(`/api/v1/admin/products/${productId}/attributes`, {
    attribute_ids: attributeIds,
  });

  return response.data;
}

export async function uploadProductImage(
  productId: string | number,
  payload: {
    image: string;
    alt?: string;
    is_primary?: boolean;
    sort_order?: number;
  }
) {
  const response = await api.post(`/api/v1/admin/products/${productId}/images`, payload);
  return normalizeItem<ProductImage>(response.data);
}

export async function deleteProductImage(productImageId: string | number) {
  const response = await api.delete(`/api/v1/admin/product-images/${productImageId}`);
  return response.data;
}

export async function setPrimaryProductImage(productImageId: string | number) {
  const response = await api.patch(`/api/v1/admin/product-images/${productImageId}/set-primary`);
  return response.data;
}