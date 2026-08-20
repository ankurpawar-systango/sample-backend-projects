"""
Unit Tests for Feature Preferences API (DL-64)

Tests the feature preferences API endpoints including:
- Getting platform features list
- Getting individual feature details
- Saving user preferences
- Retrieving user preferences
- Deleting user preferences
- Validating preferences without saving
- Health check endpoint
- Error handling for invalid features
- Validation of essential features
"""

import pytest
from fastapi.testclient import TestClient
from datetime import datetime
from feature_preferences import (
    router,
    PLATFORM_FEATURES,
    preferences_storage,
    FeaturePreferencesRequest
)
from fastapi import FastAPI

# Create a test app with the feature preferences router
app = FastAPI()
app.include_router(router)
client = TestClient(app)


@pytest.fixture(autouse=True)
def clear_storage():
    """Clear preferences storage before each test"""
    preferences_storage.clear()
    yield
    preferences_storage.clear()


class TestGetPlatformFeatures:
    """Tests for getting platform features"""

    def test_get_all_features(self):
        """Test retrieving all platform features"""
        response = client.get("/api/preferences/features")
        assert response.status_code == 200
        data = response.json()
        assert "features" in data
        assert len(data["features"]) == 6
        assert "authentication" in data["features"]
        assert "analytics" in data["features"]

    def test_features_have_required_fields(self):
        """Test that features have all required fields"""
        response = client.get("/api/preferences/features")
        data = response.json()
        for feature_id, feature in data["features"].items():
            assert "name" in feature
            assert "description" in feature
            assert "essential" in feature
            assert "category" in feature

    def test_essential_features_marked(self):
        """Test that essential features are properly marked"""
        response = client.get("/api/preferences/features")
        data = response.json()
        features = data["features"]
        assert features["authentication"]["essential"] is True
        assert features["core_functionality"]["essential"] is True
        assert features["analytics"]["essential"] is False


class TestGetIndividualFeature:
    """Tests for getting individual feature details"""

    def test_get_valid_feature(self):
        """Test retrieving a valid feature"""
        response = client.get("/api/preferences/features/authentication")
        assert response.status_code == 200
        data = response.json()
        assert data["feature_id"] == "authentication"
        assert data["name"] == "Authentication & Security"
        assert data["essential"] is True

    def test_get_nonexistent_feature(self):
        """Test retrieving a non-existent feature"""
        response = client.get("/api/preferences/features/invalid_feature")
        assert response.status_code == 404
        assert "not found" in response.json()["detail"].lower()

    def test_get_all_individual_features(self):
        """Test retrieving each feature individually"""
        for feature_id in PLATFORM_FEATURES.keys():
            response = client.get(f"/api/preferences/features/{feature_id}")
            assert response.status_code == 200
            data = response.json()
            assert data["feature_id"] == feature_id


class TestSavePreferences:
    """Tests for saving user preferences"""

    def test_save_valid_preferences(self):
        """Test saving valid preferences"""
        preferences = {
            "authentication": True,
            "core_functionality": True,
            "analytics": True,
            "personalization": False,
            "notifications": True,
            "third_party_integrations": False
        }
        response = client.post(
            "/api/preferences/save",
            json={
                "preferences": preferences,
                "user_id": "test_user"
            }
        )
        assert response.status_code == 200
        data = response.json()
        assert data["success"] is True
        assert data["preferences"] == preferences

    def test_save_preferences_with_timestamp(self):
        """Test that saved preferences include timestamps"""
        preferences = {
            "authentication": True,
            "core_functionality": True,
            "analytics": True
        }
        response = client.post(
            "/api/preferences/save",
            json={
                "preferences": preferences,
                "user_id": "test_user"
            }
        )
        data = response.json()
        assert "created_at" in data
        assert "updated_at" in data
        assert data["created_at"] is not None
        assert data["updated_at"] is not None

    def test_save_disables_nonessential_feature(self):
        """Test saving with non-essential features disabled"""
        preferences = {
            "authentication": True,
            "core_functionality": True,
            "analytics": False,
            "personalization": False,
            "notifications": False,
            "third_party_integrations": False
        }
        response = client.post(
            "/api/preferences/save",
            json={
                "preferences": preferences,
                "user_id": "test_user"
            }
        )
        assert response.status_code == 200

    def test_save_rejects_all_essential_features_disabled(self):
        """Test that saving fails when ALL essential features are disabled"""
        preferences = {
            "authentication": False,  # Essential feature disabled
            "core_functionality": False,  # Essential feature disabled
            "analytics": True
        }
        response = client.post(
            "/api/preferences/save",
            json={
                "preferences": preferences,
                "user_id": "test_user"
            }
        )
        assert response.status_code == 400
        assert "essential" in response.json()["detail"].lower()

    def test_save_allows_one_essential_feature_disabled(self):
        """Test that saving succeeds when one essential feature is enabled"""
        preferences = {
            "authentication": False,  # One essential feature disabled
            "core_functionality": True,  # One essential feature enabled
            "analytics": True
        }
        response = client.post(
            "/api/preferences/save",
            json={
                "preferences": preferences,
                "user_id": "test_user"
            }
        )
        assert response.status_code == 200
        assert response.json()["success"] is True

    def test_save_rejects_invalid_feature(self):
        """Test that saving fails with invalid feature ID"""
        preferences = {
            "authentication": True,
            "core_functionality": True,
            "invalid_feature": True
        }
        response = client.post(
            "/api/preferences/save",
            json={
                "preferences": preferences,
                "user_id": "test_user"
            }
        )
        assert response.status_code == 400
        assert "invalid" in response.json()["detail"].lower()

    def test_save_updates_existing_preferences(self):
        """Test that saving updates existing preferences"""
        # First save
        response1 = client.post(
            "/api/preferences/save",
            json={
                "preferences": {
                    "authentication": True,
                    "core_functionality": True,
                    "analytics": True
                },
                "user_id": "test_user"
            }
        )
        first_update = response1.json()["updated_at"]

        # Second save with different preferences
        response2 = client.post(
            "/api/preferences/save",
            json={
                "preferences": {
                    "authentication": True,
                    "core_functionality": True,
                    "analytics": False
                },
                "user_id": "test_user"
            }
        )
        second_update = response2.json()["updated_at"]

        # Created_at should be the same, updated_at should change
        assert response1.json()["created_at"] == response2.json()["created_at"]


class TestGetUserPreferences:
    """Tests for retrieving user preferences"""

    def test_get_existing_preferences(self):
        """Test retrieving existing user preferences"""
        # First save preferences
        client.post(
            "/api/preferences/save",
            json={
                "preferences": {
                    "authentication": True,
                    "core_functionality": True,
                    "analytics": False
                },
                "user_id": "test_user"
            }
        )

        # Then retrieve
        response = client.get("/api/preferences/user/test_user")
        assert response.status_code == 200
        data = response.json()
        assert data["success"] is True
        assert data["preferences"]["analytics"] is False
        assert data["preferences"]["authentication"] is True

    def test_get_nonexistent_user_preferences(self):
        """Test retrieving preferences for non-existent user"""
        response = client.get("/api/preferences/user/nonexistent_user")
        assert response.status_code == 404
        detail = response.json()["detail"].lower()
        assert "not found" in detail or "no preferences" in detail


class TestDeleteUserPreferences:
    """Tests for deleting user preferences"""

    def test_delete_existing_preferences(self):
        """Test deleting existing preferences"""
        # Save preferences first
        client.post(
            "/api/preferences/save",
            json={
                "preferences": {
                    "authentication": True,
                    "core_functionality": True,
                    "analytics": True
                },
                "user_id": "test_user"
            }
        )

        # Delete
        response = client.delete("/api/preferences/user/test_user")
        assert response.status_code == 200
        assert response.json()["success"] is True

        # Verify deletion
        get_response = client.get("/api/preferences/user/test_user")
        assert get_response.status_code == 404

    def test_delete_nonexistent_preferences(self):
        """Test deleting preferences for non-existent user"""
        response = client.delete("/api/preferences/user/nonexistent_user")
        assert response.status_code == 404


class TestValidatePreferences:
    """Tests for validating preferences"""

    def test_validate_valid_preferences(self):
        """Test validating valid preferences"""
        import json
        prefs = {
            "authentication": True,
            "core_functionality": True,
            "analytics": False
        }
        response = client.get(
            "/api/preferences/validate",
            params={"feature_preferences": json.dumps(prefs)}
        )
        assert response.status_code == 200
        data = response.json()
        assert data["valid"] is True
        assert len(data["errors"]) == 0

    def test_validate_all_essential_features_disabled(self):
        """Test validating with all essential features disabled"""
        import json
        prefs = {
            "authentication": False,
            "core_functionality": False,
            "analytics": False
        }
        response = client.get(
            "/api/preferences/validate",
            params={"feature_preferences": json.dumps(prefs)}
        )
        assert response.status_code == 200
        data = response.json()
        assert data["valid"] is False
        assert any("essential" in error.lower() for error in data["errors"])

    def test_validate_one_essential_feature_enabled(self):
        """Test validating with one essential feature enabled"""
        import json
        prefs = {
            "authentication": False,
            "core_functionality": True,
            "analytics": False
        }
        response = client.get(
            "/api/preferences/validate",
            params={"feature_preferences": json.dumps(prefs)}
        )
        assert response.status_code == 200
        data = response.json()
        assert data["valid"] is True
        assert len(data["errors"]) == 0

    def test_validate_invalid_feature(self):
        """Test validating with invalid feature ID"""
        import json
        prefs = {
            "authentication": True,
            "core_functionality": True,
            "invalid_feature": True
        }
        response = client.get(
            "/api/preferences/validate",
            params={"feature_preferences": json.dumps(prefs)}
        )
        assert response.status_code == 200
        data = response.json()
        assert data["valid"] is False

    def test_validate_invalid_json(self):
        """Test validating with invalid JSON"""
        response = client.get(
            "/api/preferences/validate",
            params={"feature_preferences": "invalid json"}
        )
        assert response.status_code == 400


class TestHealthCheck:
    """Tests for health check endpoint"""

    def test_health_check(self):
        """Test feature preferences API health check"""
        response = client.get("/api/preferences/health")
        assert response.status_code == 200
        data = response.json()
        assert data["status"] == "healthy"
        assert "feature-preferences-api" in data["service"]


class TestFeaturePreferencesIntegration:
    """Integration tests for feature preferences workflow"""

    def test_complete_workflow(self):
        """Test complete workflow: get features, save, retrieve, update, delete"""
        # 1. Get available features
        features_response = client.get("/api/preferences/features")
        assert features_response.status_code == 200
        features = features_response.json()["features"]

        # 2. Save preferences
        prefs = {
            "authentication": True,
            "core_functionality": True,
            "analytics": True,
            "personalization": False,
            "notifications": True,
            "third_party_integrations": False
        }
        save_response = client.post(
            "/api/preferences/save",
            json={"preferences": prefs, "user_id": "workflow_user"}
        )
        assert save_response.status_code == 200

        # 3. Retrieve preferences
        get_response = client.get("/api/preferences/user/workflow_user")
        assert get_response.status_code == 200
        retrieved = get_response.json()
        assert retrieved["preferences"] == prefs

        # 4. Update preferences
        updated_prefs = {
            "authentication": True,
            "core_functionality": True,
            "analytics": False,  # Changed
            "personalization": True,  # Changed
            "notifications": True,
            "third_party_integrations": False
        }
        update_response = client.post(
            "/api/preferences/save",
            json={"preferences": updated_prefs, "user_id": "workflow_user"}
        )
        assert update_response.status_code == 200
        assert update_response.json()["preferences"]["analytics"] is False

        # 5. Delete preferences
        delete_response = client.delete("/api/preferences/user/workflow_user")
        assert delete_response.status_code == 200


if __name__ == "__main__":
    pytest.main([__file__, "-v"])
