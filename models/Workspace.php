<?php

require_once 'models/BaseModel.php';

class Workspace extends BaseModel {

    public function create($name, $description, $owner_id, $invite_code) {
        $sql = "INSERT INTO workspaces
                (name, description, owner_id, invite_code, created_at)
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $name,
            $description,
            $owner_id,
            $invite_code
        ]);

        return $this->db->lastInsertId();
    }

    public function addMember($workspace_id, $user_id) {
        $sql = "INSERT INTO workspace_members (workspace_id, user_id, joined_at)
                VALUES (?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$workspace_id, $user_id]);
    }

    public function findByInviteCode($code) {
        $sql = "SELECT * FROM workspaces WHERE invite_code = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function isMember($workspace_id, $user_id) {
        $sql = "SELECT * FROM workspace_members WHERE workspace_id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$workspace_id, $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserWorkspaces($user_id) {
        $sql = "SELECT w.*
                FROM workspaces w
                JOIN workspace_members wm ON w.id = wm.workspace_id
                WHERE wm.user_id = ?
                ORDER BY w.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($workspace_id) {
        $sql = "SELECT * FROM workspaces WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$workspace_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getMembers($workspace_id) {
        $sql = "SELECT users.id, users.name, users.email, workspace_members.joined_at
                FROM users
                JOIN workspace_members ON users.id = workspace_members.user_id
                WHERE workspace_members.workspace_id = ?
                ORDER BY users.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$workspace_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFirstWorkspaceForUser($user_id) {
        $sql = "SELECT w.*
                FROM workspaces w
                JOIN workspace_members wm ON wm.workspace_id = w.id
                WHERE wm.user_id = ?
                ORDER BY w.created_at ASC
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
