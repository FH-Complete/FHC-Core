import ApiBookmark from '../../../api/factory/widget/bookmark.js';

const bookmarksRef = Vue.ref([]);
const tags = Vue.computed(() => {
	return bookmarksRef.value
		.map(bookmark => JSON.parse(bookmark.tag))
		.flat()
		.filter((v, i, a) => v && a.indexOf(v) === i);
});
const state = Vue.readonly(bookmarksRef);

export function useUrlStore() {
	const $api = Vue.inject('$api');
	const $fhcAlert = Vue.inject('$fhcAlert');

	async function fetch() {
		try {
			bookmarksRef.value = (await $api.call(ApiBookmark.getBookmarks())).data;
		} catch(error) {
			$fhcAlert.handleSystemError(error);
			return error;
		}
	}

	async function insert(title, url, tag, sort) {
		try {
			await $api.call(ApiBookmark.insert({ title, url, tag, sort }));
			await fetch();
		} catch(error) {
			$fhcAlert.handleSystemError(error);
			return error;
		}
	}

	async function update(bookmark_id, title, url, tag) {
		try {
			await $api.call(ApiBookmark.update({ bookmark_id, title, url, tag }));
			await fetch();
		} catch(error) {
			$fhcAlert.handleSystemError(error);
			return error;
		}
	}

	async function swap(bookmark_id_1, bookmark_id_2) {
		try {
			await $api.call(ApiBookmark.changeOrder(bookmark_id_1, bookmark_id_2));
			await fetch();
		} catch(error) {
			$fhcAlert.handleSystemError(error);
			return error;
		}
	}

	async function remove(bookmark_id) {
		try {
			await $api.call(ApiBookmark.delete(bookmark_id));
			await fetch();
		} catch(error) {
			$fhcAlert.handleSystemError(error);
			return error;
		}
	}

	return {
		bookmarks: state,
		getters: {
			tags
		},
		actions: {
			fetch,
			insert,
			update,
			swap,
			remove
		}
	};
}