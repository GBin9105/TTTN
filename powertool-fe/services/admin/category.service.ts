import { api } from "../../lib/api";

export type Category = {
  id: number;
  parent_id?: number | null;
  name: string;
  slug?: string | null;
  description?: string | null;
  image?: string | null;
  status?: boolean | number;
  sort_order?: number | null;
  created_at?: string;
  updated_at?: string;
};

export type CategoryFormPayload = {
  parent_id: number | null;
  name: string;
  slug: string;
  description: string;
  image: string;
  status: boolean;
  sort_order: number;
};

export type CategoryListParams = {
  page?: number;
  per_page?: number;
  q?: string;
  parent_id?: string | number;
  status?: string;
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

function normalizePaginated<T>(
  payload: ApiEnvelope<PaginatedResult<T>> | PaginatedResult<T> | T[]
): PaginatedResult<T> {
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
    data: Array.isArray((data as PaginatedResult<T>)?.data)
      ? (data as PaginatedResult<T>).data
      : [],
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

export async function getCategories(params: CategoryListParams = {}) {
  const response = await api.get("/api/v1/admin/categories", { params });
  return normalizePaginated<Category>(response.data);
}

export async function getCategory(id: string | number) {
  const response = await api.get(`/api/v1/admin/categories/${id}`);
  return normalizeItem<Category>(response.data);
}

export async function createCategory(payload: CategoryFormPayload) {
  const response = await api.post("/api/v1/admin/categories", payload);
  return normalizeItem<Category>(response.data);
}

export async function updateCategory(id: string | number, payload: CategoryFormPayload) {
  const response = await api.put(`/api/v1/admin/categories/${id}`, payload);
  return normalizeItem<Category>(response.data);
}

export async function deleteCategory(id: string | number) {
  const response = await api.delete(`/api/v1/admin/categories/${id}`);
  return response.data;
}

export async function getCategoriesForSelect() {
  const result = await getCategories({ per_page: 1000 });
  return result.data;
}