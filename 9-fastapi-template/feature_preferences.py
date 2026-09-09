"""
Feature Preferences API Module (DL-64)

Provides endpoints for managing user feature preferences.
Feature preferences allow users to customize which platform features they consent to use.

Features tracked:
- authentication: Core authentication and security features
- core_functionality: Essential platform operations
- analytics: Usage tracking and analytics
- personalization: User preferences and customization
- notifications: Alerts and notifications
- third_party_integrations: External service integrations
"""

from fastapi import APIRouter, HTTPException, Query
from pydantic import BaseModel, Field
from typing import Optional, Dict, Any
from datetime import datetime

# Create router for feature preferences endpoints
router = APIRouter(
    prefix="/api/preferences",
    tags=["Feature Preferences"]
)

# Pydantic models
class FeaturePreference(BaseModel):
    """Individual feature preference"""
    feature_id: str = Field(..., description="Unique identifier for the feature")
    enabled: bool = Field(..., description="Whether the feature is enabled")
    name: Optional[str] = Field(None, description="Display name of the feature")
    description: Optional[str] = Field(None, description="Description of what the feature does")
    essential: bool = Field(False, description="Whether this is an essential feature")
    category: Optional[str] = Field(None, description="Category of the feature")


class FeaturePreferencesRequest(BaseModel):
    """Request body for saving feature preferences"""
    preferences: Dict[str, bool] = Field(
        ...,
        description="Dictionary of feature_id -> enabled boolean"
    )
    user_id: Optional[str] = Field(None, description="Optional user identifier")
    timestamp: Optional[str] = Field(
        default_factory=lambda: datetime.utcnow().isoformat(),
        description="Timestamp when preferences were set"
    )


class FeaturePreferencesResponse(BaseModel):
    """Response for feature preferences operations"""
    success: bool = Field(..., description="Whether the operation was successful")
    message: str = Field(..., description="Operation message")
    preferences: Optional[Dict[str, bool]] = Field(
        None,
        description="Current feature preferences"
    )
    timestamp: Optional[str] = Field(
        None,
        description="Timestamp of the operation"
    )
    created_at: Optional[str] = Field(None, description="Creation timestamp")
    updated_at: Optional[str] = Field(None, description="Last update timestamp")


class PlatformFeatures(BaseModel):
    """Platform features configuration"""
    features: Dict[str, Dict[str, Any]] = Field(..., description="Available platform features")


# In-memory storage for feature preferences (in production, use a database)
preferences_storage: Dict[str, Dict[str, Any]] = {}

# Platform features configuration
PLATFORM_FEATURES = {
    "authentication": {
        "name": "Authentication & Security",
        "description": "Core authentication, login sessions, and security features to protect your account",
        "essential": True,
        "category": "Core"
    },
    "core_functionality": {
        "name": "Core Platform Functionality",
        "description": "Essential features required for the platform to operate (data storage, profile management)",
        "essential": True,
        "category": "Core"
    },
    "analytics": {
        "name": "Analytics & Usage Tracking",
        "description": "Track your usage patterns to help us understand feature adoption and improve the platform",
        "essential": False,
        "category": "Analytics"
    },
    "personalization": {
        "name": "Personalization & Preferences",
        "description": "Customize your experience based on your preferences, language, theme, and saved settings",
        "essential": False,
        "category": "User Experience"
    },
    "notifications": {
        "name": "Notifications & Alerts",
        "description": "Send you important alerts, updates, and notifications about your activity",
        "essential": False,
        "category": "Communications"
    },
    "third_party_integrations": {
        "name": "Third-Party Integrations",
        "description": "Connect with external services and integrations to extend platform functionality",
        "essential": False,
        "category": "Integrations"
    }
}


@router.get("/features", response_model=PlatformFeatures)
async def get_platform_features():
    """
    Get list of all available platform features with their metadata.

    Returns:
        PlatformFeatures: Dictionary of available features with descriptions, categories, and essentiality
    """
    return PlatformFeatures(features=PLATFORM_FEATURES)


@router.get("/features/{feature_id}", response_model=FeaturePreference)
async def get_feature(feature_id: str):
    """
    Get details about a specific platform feature.

    Args:
        feature_id: The identifier of the feature to retrieve

    Returns:
        FeaturePreference: Details about the requested feature

    Raises:
        HTTPException: If the feature is not found
    """
    if feature_id not in PLATFORM_FEATURES:
        raise HTTPException(status_code=404, detail=f"Feature '{feature_id}' not found")

    feature = PLATFORM_FEATURES[feature_id]
    return FeaturePreference(
        feature_id=feature_id,
        enabled=True,  # Default enabled
        **feature
    )


@router.post("/save", response_model=FeaturePreferencesResponse)
async def save_preferences(request: FeaturePreferencesRequest):
    """
    Save user feature preferences.

    Validates that:
    - All preference keys are valid feature IDs
    - At least one essential feature remains enabled

    Args:
        request: Feature preferences to save

    Returns:
        FeaturePreferencesResponse: Confirmation with saved preferences

    Raises:
        HTTPException: If validation fails
    """
    # Validate all feature IDs exist
    for feature_id in request.preferences.keys():
        if feature_id not in PLATFORM_FEATURES:
            raise HTTPException(
                status_code=400,
                detail=f"Invalid feature ID: '{feature_id}'"
            )

    # Validate at least one essential feature is enabled
    essential_features = [
        fid for fid, feature in PLATFORM_FEATURES.items()
        if feature.get("essential", False)
    ]

    # Check if ALL essential features have at least one enabled
    all_essential_enabled = all(
        request.preferences.get(fid, True)
        for fid in essential_features
    )

    # For strict validation: at least one essential feature must be enabled
    has_enabled_essential = any(
        request.preferences.get(fid, True)
        for fid in essential_features
    )

    if not has_enabled_essential:
        raise HTTPException(
            status_code=400,
            detail="At least one essential feature must remain enabled"
        )

    # Store preferences
    user_id = request.user_id or "anonymous"
    timestamp = request.timestamp or datetime.utcnow().isoformat()

    preferences_storage[user_id] = {
        "preferences": request.preferences,
        "created_at": preferences_storage.get(user_id, {}).get("created_at", timestamp),
        "updated_at": timestamp
    }

    return FeaturePreferencesResponse(
        success=True,
        message="Feature preferences saved successfully",
        preferences=request.preferences,
        timestamp=timestamp,
        created_at=preferences_storage[user_id].get("created_at"),
        updated_at=timestamp
    )


@router.get("/user/{user_id}", response_model=FeaturePreferencesResponse)
async def get_user_preferences(user_id: str):
    """
    Retrieve feature preferences for a specific user.

    Args:
        user_id: The user identifier

    Returns:
        FeaturePreferencesResponse: User's feature preferences if found

    Raises:
        HTTPException: If user preferences not found
    """
    if user_id not in preferences_storage:
        raise HTTPException(
            status_code=404,
            detail=f"No preferences found for user '{user_id}'"
        )

    prefs = preferences_storage[user_id]
    return FeaturePreferencesResponse(
        success=True,
        message="Feature preferences retrieved successfully",
        preferences=prefs.get("preferences"),
        created_at=prefs.get("created_at"),
        updated_at=prefs.get("updated_at")
    )


@router.delete("/user/{user_id}", response_model=FeaturePreferencesResponse)
async def delete_user_preferences(user_id: str):
    """
    Delete feature preferences for a specific user.

    Args:
        user_id: The user identifier

    Returns:
        FeaturePreferencesResponse: Confirmation of deletion

    Raises:
        HTTPException: If user preferences not found
    """
    if user_id not in preferences_storage:
        raise HTTPException(
            status_code=404,
            detail=f"No preferences found for user '{user_id}'"
        )

    del preferences_storage[user_id]
    return FeaturePreferencesResponse(
        success=True,
        message=f"Feature preferences deleted for user '{user_id}'"
    )


@router.get("/validate")
async def validate_preferences(
    feature_preferences: str = Query(
        ...,
        description="JSON string of feature preferences to validate"
    )
):
    """
    Validate feature preferences without saving them.

    Args:
        feature_preferences: JSON string of feature_id -> boolean mappings

    Returns:
        dict: Validation result with success status and any errors
    """
    import json

    try:
        prefs = json.loads(feature_preferences)
    except json.JSONDecodeError:
        raise HTTPException(status_code=400, detail="Invalid JSON format")

    if not isinstance(prefs, dict):
        raise HTTPException(status_code=400, detail="Preferences must be a dictionary")

    errors = []

    # Validate all feature IDs
    for feature_id in prefs.keys():
        if feature_id not in PLATFORM_FEATURES:
            errors.append(f"Unknown feature ID: '{feature_id}'")

    # Check essential features
    essential_features = [
        fid for fid, feature in PLATFORM_FEATURES.items()
        if feature.get("essential", False)
    ]

    has_enabled_essential = any(
        prefs.get(fid, True)
        for fid in essential_features
    )

    if not has_enabled_essential:
        errors.append("At least one essential feature must remain enabled")

    return {
        "valid": len(errors) == 0,
        "errors": errors,
        "validated_count": len(prefs)
    }


@router.get("/health")
async def health_check():
    """Health check endpoint for feature preferences API"""
    return {
        "status": "healthy",
        "service": "feature-preferences-api",
        "version": "1.0.0"
    }
