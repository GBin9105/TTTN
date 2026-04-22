import ProductForm from "../../_components/ProductForm";

export default async function AdminEditProductPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;

  return <ProductForm mode="edit" productId={id} />;
}