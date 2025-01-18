import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import {
	PanelBody,
	SelectControl,
	RangeControl,
	Spinner,
} from "@wordpress/components";
import { useEffect, useState } from "@wordpress/element";
import apiFetch from "@wordpress/api-fetch";
import "./editor.scss";

export default function Edit({ attributes, setAttributes }) {
	const { order, numberOfUsers } = attributes;
	const [users, setUsers] = useState([]);
	const [isLoading, setIsLoading] = useState(true);

	useEffect(() => {
		fetchUsers();
	}, [numberOfUsers]);

	const fetchUsers = async () => {
		setIsLoading(true);
		try {
			const response = await apiFetch({
				path: `/ahmedyahya/v1/top-users?limit=${numberOfUsers}`,
			});
			setUsers(response);
		} catch (error) {
			console.error("Error fetching users:", error);
		}
		setIsLoading(false);
	};

	const sortedUsers = [...users].sort((a, b) => {
		return order === "asc"
			? a.total_order_value - b.total_order_value
			: b.total_order_value - a.total_order_value;
	});

	return (
		<div {...useBlockProps()}>
			<InspectorControls>
				<PanelBody title={__("Block Settings", "top-users-block")}>
					<SelectControl
						label={__("Order", "top-users-block")}
						value={order}
						options={[
							{ label: __("Ascending", "top-users-block"), value: "asc" },
							{ label: __("Descending", "top-users-block"), value: "desc" },
						]}
						onChange={(newOrder) => setAttributes({ order: newOrder })}
					/>
					<RangeControl
						label={__("Number of Users", "top-users-block")}
						value={numberOfUsers}
						onChange={(newNumber) =>
							setAttributes({ numberOfUsers: newNumber })
						}
						min={1}
						max={20}
					/>
				</PanelBody>
			</InspectorControls>

			<h2>{__(`Top ${numberOfUsers} Users`, "top-users-block")}</h2>
			{isLoading ? (
				<Spinner />
			) : (
				<ul className="top-users-list">
					{sortedUsers.map((user) => (
						<li key={user.id}>
							<strong>{user.name}</strong>: Total orders amount{" "}
							{user.total_order_value}
						</li>
					))}
				</ul>
			)}
		</div>
	);
}
