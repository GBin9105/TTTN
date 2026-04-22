import AttributeForm from "../../_components/AttributeForm";

export default async function AdminEditAttributePage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;

  return <AttributeForm mode="edit" attributeId={id} />;
}