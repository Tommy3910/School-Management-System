@echo off
title GradeInsightsService (ML.NET C# ASP.NET Core)
echo ===================================================
echo Starting Grade Insights Microservice (ML.NET)
echo Endpoint: http://localhost:5000/api/insights/predict
echo ===================================================
dotnet --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] .NET SDK is not found on your system PATH.
    echo Please install .NET 8 SDK from https://dotnet.microsoft.com/download
    pause
    exit /b 1
)

cd /d "%~dp0"
echo Restoring NuGet packages...
dotnet restore
echo Building and running service...
dotnet run
pause
