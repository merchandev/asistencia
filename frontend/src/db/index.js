import Dexie from 'dexie';

export const db = new Dexie('AttendanceDB');

db.version(1).stores({
  pendingPunches: '++id, employeeId, branch_code, punch_type, latitude, longitude, device_timestamp, synced'
});

export async function saveOfflinePunch(punchData) {
  return await db.pendingPunches.add({
    ...punchData,
    synced: 0, // Usar 0 para boolean en indexeddb
  });
}

export async function getPendingPunches() {
  return await db.pendingPunches.where('synced').equals(0).toArray();
}

export async function markAsSynced(id) {
  await db.pendingPunches.update(id, { synced: 1 });
}

export async function removeSyncedPunch(id) {
  await db.pendingPunches.delete(id);
}
