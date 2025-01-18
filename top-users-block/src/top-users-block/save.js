import { useBlockProps } from "@wordpress/block-editor";

export default function save({ attributes }) {
	const { order, numberOfUsers } = attributes;
	return (
		<div {...useBlockProps.save()}>
			<div
				className="top-users-block-container"
				data-order={order}
				data-number-of-users={numberOfUsers}
			></div>
		</div>
	);
}
