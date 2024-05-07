#include "framework.h"
#include "FabDoubleFinder b1_0.h"
#include <ShlObj.h>  
#include <commctrl.h>
#include <iostream>
#include <string>
#include <unordered_set>
#include <unordered_map>
#include <wincrypt.h>
#include <boost/filesystem.hpp>
#include <thread>
#include <chrono>
#include <mutex>
#include <algorithm>
#include <openssl/sha.h>
#include <fstream>
#include <cmath>
#include <future>
#include <vector>
#include <chrono>
#include <windows.h>
#include <cstdlib>
#include <cstring>
#include <CommCtrl.h>
#include <algorithm>

static HWND hComboBox3 = NULL;
static HWND hComboBox2 = NULL;
static HWND hComboBox = NULL;
static HWND hSlider = NULL;
static HWND hwndButtonproc = NULL;
static HWND hwndButtonThrdCountb = NULL;
static HWND hwndButtonThrdCount = NULL;
static HWND hwndButtonS = NULL;
static HWND hwndButton = NULL;
static HWND hwndButton2 = NULL;
static HWND hwndButton3 = NULL;
static HWND hEdit = NULL;
static HWND hwndButtonDelete = NULL;
static HWND hwndButtonIgnore = NULL;
static HWND hwndButtonSizeTotal = NULL;
static HWND hwndButtonFileCount = NULL;
static HWND hwndButtonFilesCount = NULL;
static HWND hwndButtonprocS = NULL;
static HWND hwndButtonDeleteAll = NULL;
 std::atomic<bool> searchD = false;
 std::atomic<bool> backWin = true;
 std::wstring duplicatesTexterr = L"";
#define IDC_COMBO 4222
#define IDC_COMBO2 42222
#define IDC_COMBO3 422222
#define IDC_SLIDER        4333
#define IDM_SELECT_FOLDER 1001
#define IDM_SELECT_FOLDER2 10010
#define IDM_SELECT_FOLDER3 10011
#define IDM_SEARCH_DUPLICATES 1002
#define IDC_EDIT 101
#define IDM_DELETE_DUPLICATE 1003
#define IDM_IGNORE_DUPLICATE 1004
// Identifiants de contrôles pour les boutons que vous devez définir
#define IDC_BUTTON_SIZE_TOTAL 102
#define IDC_BUTTON_FILE_COUNT 103
#define IDM_DELETE_ALL_DUPLICATES 1005

#define MAX_LOADSTRING 100

// Variables globales :
 std::vector<int> threadReturnCodes;
 double coefProc = 0.0; // pour 
 std::atomic<int> recursiveCallsCounter(1);

 std::vector<std::thread> threads;
 //std::vector<std::unique_ptr<std::thread>> threads;
 HANDLE mutex;
 TCHAR coefProcS[50];
DWORD iterationS = 0;
DWORD iterationthrd = 0;
TCHAR iterationthrdStr[50];
DWORD iterationthrdb = 0;
TCHAR iterationthrdStrb[50];
DWORD iterationSb = 0;
bool continueLoop = true;
DWORD Taille_total = 0;
DWORD nombre_de_Fichiers_total = 0;
TCHAR tailleTotalStr[50];
TCHAR tailleTotalStrs[50];
TCHAR nombreFichiersTotalStr[50];
static HWND hwndButtonFilespCount = NULL;
TCHAR tailleTotalStrsp[50];
int procSeuil=20;
std::vector<std::wstring> subDirectories;
std::mutex subDirectoriesMutex;

TCHAR pathSelected[MAX_PATH];
TCHAR pathSelected2[MAX_PATH];
TCHAR pathSelected3[MAX_PATH];
INT reversS = 1;
int selectedIndex;
int selectedIndexAnc;
int selectedIndex2;
int selectedIndexAnc2;
int itbA = 0;// création
int itbB = 0;// threads terminée hash rempli
std::vector<std::wstring> SelectS;

HINSTANCE hInst;                                // instance actuelle
WCHAR szTitle[MAX_LOADSTRING];                  // Texte de la barre de titre
WCHAR szWindowClass[MAX_LOADSTRING];            // nom de la classe de fenêtre principale

// Déclarations anticipées des fonctions incluses dans ce module de code :
ATOM                MyRegisterClass(HINSTANCE hInstance);
BOOL                InitInstance(HINSTANCE, int);
LRESULT CALLBACK    WndProc(HWND, UINT, WPARAM, LPARAM);
INT_PTR CALLBACK    About(HWND, UINT, WPARAM, LPARAM);

struct FileInfo {
    std::wstring filePath;
    uint64_t fileSize;
    std::vector<unsigned char> hash;

    // Constructeur avec deux arguments
    FileInfo(const std::wstring& path, uint64_t size) : filePath(path), fileSize(size) {}

    // Constructeur avec trois arguments
    FileInfo(const std::wstring& path, uint64_t size, const std::vector<unsigned char>& h) : filePath(path), fileSize(size), hash(h) {}
  
};
std::vector<FileInfo> allFiles;

std::mutex allFilesMutex;
std::vector<FileInfo> duplicatePaths;
std::vector<FileInfo> duplicatePathsO;
void afficher(std::wstring duplicatesTexterrin) {

    duplicatesTexterr = duplicatesTexterrin + duplicatesTexterr;
    SendMessageW(hEdit, WM_SETTEXT, 0, (LPARAM)duplicatesTexterr.c_str());
}
void SwapDuplicatePaths(std::wstring inputPath)
{
    FileInfo caract(L"",0);
    std::wstring patho;  std::wstring path= inputPath;
    int indD=-10;
    //touver dbl 
     // Wait for some time
   // afficher(L" <in swap> "+ inputPath); Sleep(3000);
    //sauve file info caracteristique
    // // trouver ori string
    //chancher duplipath de o à d et de d à o
    // trouver
    //
    for (size_t i = 0; i < duplicatePaths.size(); ++i) {
        if (duplicatePaths[i].filePath==inputPath) {
            caract = duplicatePaths[i]; 
            indD = i;  
            //afficher(L" <matched!> "); Sleep(3000);
        }
    }
    if (indD > -1){
        // si trouvé alors on trouve l'original et on switch
        for (size_t i = 0; i < duplicatePathsO.size(); ++i) {
            if ((duplicatePathsO[i].hash == caract.hash) && (duplicatePathsO[i].fileSize == caract.fileSize)) {
                patho = duplicatePathsO[i].filePath;
                duplicatePathsO[i].filePath = inputPath; duplicatePaths[indD].filePath = patho;
                //afficher(L" <switched> O et D "+ patho+L" "+ inputPath);
            }
        }
    }
    
}

std::wstring doublerBackslashes(const std::wstring& input) {
    std::wstring result;

    for (wchar_t ch : input) {
        if (ch == L'\\') {
            result += L"\\\\";
        }
        else {
            result += ch;
        }
    }

    return result;
}

int GetSystemCpuUsage()
{
    FILETIME idleTime, kernelTime, userTime;
    GetSystemTimes(&idleTime, &kernelTime, &userTime);

    // Store the current times
    ULARGE_INTEGER ul_idleTime, ul_kernelTime, ul_userTime;
    ul_idleTime.LowPart = idleTime.dwLowDateTime;
    ul_idleTime.HighPart = idleTime.dwHighDateTime;
    ul_kernelTime.LowPart = kernelTime.dwLowDateTime;
    ul_kernelTime.HighPart = kernelTime.dwHighDateTime;
    ul_userTime.LowPart = userTime.dwLowDateTime;
    ul_userTime.HighPart = userTime.dwHighDateTime;

    // Wait for some time
    Sleep(1000);

    // Get the new times
    GetSystemTimes(&idleTime, &kernelTime, &userTime);

    // Calculate the differences
    ULARGE_INTEGER ul_idleDiff, ul_kernelDiff, ul_userDiff;
    ul_idleDiff.QuadPart = (idleTime.dwLowDateTime - ul_idleTime.LowPart) + ((idleTime.dwHighDateTime - ul_idleTime.HighPart) * 4294967296);
    ul_kernelDiff.QuadPart = (kernelTime.dwLowDateTime - ul_kernelTime.LowPart) + ((kernelTime.dwHighDateTime - ul_kernelTime.HighPart) * 4294967296);
    ul_userDiff.QuadPart = (userTime.dwLowDateTime - ul_userTime.LowPart) + ((userTime.dwHighDateTime - ul_userTime.HighPart) * 4294967296);

    // Calculate the CPU usage
    double cpuUsage = 100.0 - (ul_idleDiff.QuadPart * 100.0 / (ul_kernelDiff.QuadPart + ul_userDiff.QuadPart));

    return static_cast<int>(std::round(cpuUsage));
}
// Fonction pour obtenir l'utilisation du CPU pour chaque processeur logique
void GetcoefProc() {
    std::vector<double> cpuUsage;

    SYSTEM_INFO sysInfo;
    GetSystemInfo(&sysInfo);

    DWORD dwProcessorCount = sysInfo.dwNumberOfProcessors;
    cpuUsage.resize(dwProcessorCount);

    for (DWORD i = 0; i < dwProcessorCount; ++i) {
        FILETIME idleTime, kernelTime, userTime;
        if (GetSystemTimes(&idleTime, &kernelTime, &userTime)) {
            ULONGLONG idleTime64 = ((ULONGLONG)idleTime.dwHighDateTime << 32) | idleTime.dwLowDateTime;
            ULONGLONG kernelTime64 = ((ULONGLONG)kernelTime.dwHighDateTime << 32) | kernelTime.dwLowDateTime;
            ULONGLONG userTime64 = ((ULONGLONG)userTime.dwHighDateTime << 32) | userTime.dwLowDateTime;

            // Calculer l'utilisation du processeur pour le cœur actuel
            ULONGLONG totalTime = kernelTime64 + userTime64;
            cpuUsage[i] =  (1.0 - ((double)idleTime64 / (double)totalTime));
        }
        else {
            std::cerr << "Erreur lors de la récupération des informations sur le CPU." << std::endl;
            cpuUsage[i] = -1.0; // Valeur négative pour indiquer une erreur
        }
    }

    // Calculer la moyenne des valeurs dans cpuUsage
    double totalUsage = 0.0;
    for (double usage : cpuUsage) {
        if (usage >= 0.0) {
            totalUsage += usage;
        }
    }

    if (cpuUsage.size() > 0) {
        coefProc = totalUsage / cpuUsage.size();
    }
    else {
        coefProc = -1.0; // Une valeur négative pour indiquer une erreur
    }
}
void eraseFillSelectS(std::vector<FileInfo>& allFiles, const std::vector<std::wstring>& SelectS) {
    // Filtrer les fichiers multimédias
    allFiles.erase(std::remove_if(allFiles.begin(), allFiles.end(), [&SelectS](const FileInfo& file) {
        std::wstring filePath = file.filePath;
        size_t lastDotPos = filePath.find_last_of(L".");
        //if (lastDotPos != std::wstring::npos) {
            std::wstring fileExtension = filePath.substr(lastDotPos);
            return std::find(SelectS.begin(), SelectS.end(), fileExtension) == SelectS.end();
      //  }
        return true; // Supprimer les fichiers sans extension
        }), allFiles.end());
}
void FillSelectSb() {
    // Images
    SelectS.push_back(L".jpg");
    SelectS.push_back(L".jpeg");
    SelectS.push_back(L".png");
    SelectS.push_back(L".gif");
    SelectS.push_back(L".bmp");
    SelectS.push_back(L".tif");
    SelectS.push_back(L".tiff");

    // Vidéos
    SelectS.push_back(L".mp4");
    SelectS.push_back(L".avi");
    SelectS.push_back(L".mkv");
    SelectS.push_back(L".mov");
    SelectS.push_back(L".wmv");
    SelectS.push_back(L".flv");

    // Audio
    SelectS.push_back(L".mp3");
    SelectS.push_back(L".wav");
    SelectS.push_back(L".ogg");
    SelectS.push_back(L".flac");
    SelectS.push_back(L".aac");


    // Autres formats multimédias courants
    SelectS.push_back(L".svg");
    SelectS.push_back(L".webp");
    SelectS.push_back(L".3gp");
    SelectS.push_back(L".m4v");

    // Documents PDF
    SelectS.push_back(L".pdf");

    // Documents Word
    SelectS.push_back(L".doc");
    SelectS.push_back(L".docx"); // Extension pour les fichiers Word modernes

    // Documents texte
    SelectS.push_back(L".txt");

    // Formats OpenOffice
    SelectS.push_back(L".odt"); // OpenDocument Text
    SelectS.push_back(L".ods"); // OpenDocument Spreadsheet
    SelectS.push_back(L".odp"); // OpenDocument Presentation

    // Bases de données
    SelectS.push_back(L".mdb"); // Microsoft Access
    SelectS.push_back(L".accdb"); // Microsoft Access (version récente)
    SelectS.push_back(L".sqlite");
    SelectS.push_back(L".db"); // Fichiers de base de données génériques

}
void FillSelectS() {
    // Images
    SelectS.push_back(L".jpg");
    SelectS.push_back(L".jpeg");
    SelectS.push_back(L".png");
    SelectS.push_back(L".gif");
    SelectS.push_back(L".bmp");
    SelectS.push_back(L".tif");
    SelectS.push_back(L".tiff");

    // Vidéos
    SelectS.push_back(L".mp4");
    SelectS.push_back(L".avi");
    SelectS.push_back(L".mkv");
    SelectS.push_back(L".mov");
    SelectS.push_back(L".wmv");
    SelectS.push_back(L".flv");

    // Audio
    SelectS.push_back(L".mp3");
    SelectS.push_back(L".wav");
    SelectS.push_back(L".ogg");
    SelectS.push_back(L".flac");
    SelectS.push_back(L".aac");

 

    // Autres formats multimédias courants
    SelectS.push_back(L".svg");
    SelectS.push_back(L".webp");
    SelectS.push_back(L".3gp");
    SelectS.push_back(L".m4v");

    // Ajoutez d'autres extensions multimédias au besoin
}

void GetcoefProcanc() {
    FILETIME idleTime, kernelTime, userTime;

    if (GetSystemTimes(&idleTime, &kernelTime, &userTime)) {
        ULONGLONG idleTime64 = ((ULONGLONG)idleTime.dwHighDateTime << 32) | idleTime.dwLowDateTime;
        ULONGLONG kernelTime64 = ((ULONGLONG)kernelTime.dwHighDateTime << 32) | kernelTime.dwLowDateTime;
        ULONGLONG userTime64 = ((ULONGLONG)userTime.dwHighDateTime << 32) | userTime.dwLowDateTime;

        // Calculer l'utilisation du processeur
        ULONGLONG totalTime = kernelTime64 + userTime64;
        coefProc =  (1.0 - ((double)idleTime64 / (double)totalTime));
    }
    else {
        std::cerr << "Erreur lors de la récupération des informations sur le CPU." << std::endl;
        coefProc = 0.1; // Une valeur négative pour indiquer une erreur
    }
}


void ListSubdirectories(const std::wstring& directory) {
    boost::filesystem::path dirPath(directory);


    try {
        if (!boost::filesystem::is_directory(dirPath)) {
            // Handle error
            return;
        }
        subDirectories.push_back(dirPath.wstring());
        for (auto& entry : boost::filesystem::directory_iterator(dirPath)) {
            
            if (!continueLoop) {
                break;  // Quitter la boucle si continueLoop est faux
            }
            iterationS += 1;
            _stprintf_s(tailleTotalStrsp, _T("%u candidats"), iterationS);
            SetWindowText(hwndButtonFilespCount, tailleTotalStrsp);
            if (boost::filesystem::is_directory(entry)) {
                if (entry.path().filename() != L"." && entry.path().filename() != L"..") {
                    //std::lock_guard<std::mutex> lock(subDirectoriesMutex);
                    //subDirectoriesMutex.lock();
                    //subDirectories.push_back(entry.path().wstring());
                   // subDirectoriesMutex.unlock();
               
                    recursiveCallsCounter++;
     
                          ListSubdirectories(entry.path().wstring());
                          recursiveCallsCounter--;
                 
                }
            }
            else
            {
                std::wstring filePath = entry.path().wstring();
                uintmax_t fileSize = boost::filesystem::file_size(entry);
                //std::lock_guard<std::mutex> lock(allFilesMutex);
                allFiles.emplace_back(filePath, fileSize);
            }
        }
   
        _stprintf_s(tailleTotalStrs, _T("%u Scans"), iterationS);
    }
    catch (const std::exception& ex) {

        std::wstring errorString = L"Erreur lors de l'ouverture d'un Fichier/Rep  list all files in dir and sub dir: ";
        errorString += std::wstring(ex.what(), ex.what() + strlen(ex.what()));
        errorString += L"\r\n";
    
        afficher(errorString);
        //std::wstring errorString = L"Erreur lors de l'ouverture d'un répertoire : ";
        //if (continueLoop){ errorString += L"vrais "; }
        //if (!continueLoop) { errorString += L"faux "; }
        errorString += std::wstring(ex.what(), ex.what() + strlen(ex.what()));
        errorString += L"\r\n";
        // MessageBox(NULL,errorString.c_str(), L"Recherche des sous-dossiers", MB_ICONWARNING | MB_OK);
        int resultBoxdefsub = MessageBox(NULL, L"Voulez-vous continuer ?", errorString.c_str(), MB_ICONQUESTION | MB_YESNO);
        std::wstring resultString = L"Result : " + std::to_wstring(iterationS);//resultBoxdefsub
        MessageBox(NULL, resultString.c_str(), L"Recherche des sous-dossiers", MB_ICONWARNING | MB_OK);

        if (resultBoxdefsub == 6) {
            // L'utilisateur a choisi "Oui"
            // Mettez votre code de traitement ici
            //std::wstring resultString = L"Résultat : " + std::to_wstring(resultBoxdefsub);
            //MessageBox(NULL,L"=6", L"Recherche des sous-dossiers", MB_ICONWARNING | MB_OK);
        }
        else {
            //if (continueLoop) {
              //  MessageBox(NULL, L"vrais", L"Recherche des sous-dossiers", MB_ICONWARNING | MB_OK);
            //}
            //if (!continueLoop) {
              //  MessageBox(NULL, L"Faux", L"Recherche des sous-dossiers", MB_ICONWARNING | MB_OK);
            //}
            continueLoop = false;
            //std::wstring resultString = L"Résultat : " + std::to_wstring(resultBoxdefsub);
            //MessageBox(NULL, L"Else", L"Recherche des sous-dossiers", MB_ICONWARNING | MB_OK);
            //if (continueLoop) {
              //  MessageBox(NULL, L"vrais", L"Recherche des sous-dossiers", MB_ICONWARNING | MB_OK);
           // }
            //if (!continueLoop) {
              //  MessageBox(NULL, L"Faux", L"Recherche des sous-dossiers", MB_ICONWARNING | MB_OK);
            //}
            // L'utilisateur a choisi "Non" ou a fermé la boîte de dialogue
            // Mettez votre code de traitement ici
        }
    }
}
void buGfileinfoisgood(const std::vector<FileInfo>& allFiles, std::wstring txtIn) {
    std::wstring errorString =  L"\r\n---------------------------------\r\n";
    errorString += txtIn + L"\r\n";
    errorString += L"-------------------------\r\n";

    for (const FileInfo& fileInfo : allFiles) {
        errorString += L"Chemin du fichier : " + fileInfo.filePath + L"\r\n";
        errorString += L"Taille du fichier : " + std::to_wstring(fileInfo.fileSize) + L" octets\r\n";
        errorString += L"Hash du fichier : [";

        // Ajoutez les valeurs du vecteur hash à la chaîne
        for (size_t i = 0; i < fileInfo.hash.size(); ++i) {
            errorString += std::to_wstring(fileInfo.hash[i]);
            if (i < fileInfo.hash.size() - 1) {
                errorString += L", ";
            }
        }

        errorString += L"]\r\n\r\n";
    }

    afficher(errorString);

    std::this_thread::sleep_for(std::chrono::milliseconds(10000));
}

void RemoveNonDuplicates() {

    //buGfileinfoisgood(allFiles, L"filtre remove double before prossessing ");

    std::vector<uintmax_t> sizes;
    sizes.reserve(allFiles.size());

    // Obtenir la liste des tailles de fichiers
    for (const auto& fileInfo : allFiles) {
        sizes.push_back(fileInfo.fileSize);
    }

    // Tri des tailles de fichiers
    std::sort(sizes.begin(), sizes.end());

    // Trouver les tailles de fichiers en double
    std::vector<uintmax_t> duplicates;
    duplicates.reserve(sizes.size());

    for (size_t i = 1; i < sizes.size(); ++i) {
        if (sizes[i] == sizes[i - 1]) {
            duplicates.push_back(sizes[i]);
        }
    }

    // Supprimer les fichiers qui n'ont pas de doublon de taille
    allFiles.erase(std::remove_if(allFiles.begin(), allFiles.end(), [&](const FileInfo& fileInfo) {
        return std::find(duplicates.begin(), duplicates.end(), fileInfo.fileSize) == duplicates.end();
        }), allFiles.end());
    SelectS.clear();
    if (selectedIndex2 == 0) {
        FillSelectS();
    }
    if (selectedIndex2 == 1) {
        FillSelectSb();
    }
    if (selectedIndex2 != 2) {
        eraseFillSelectS(allFiles, SelectS);
    }

    //std::wstring errorString = L"filtre selection :" + std::to_wstring(selectedIndex2) + L" pourcent";

    //errorString += L"\r\n";

    //afficher(errorString);
   //errorString = L"filtre selection :";

    //for (const std::wstring& extension : SelectS) {
      //  errorString += L" " + extension;
    //}

    //errorString += L" list exte";
    //errorString += L"\r\n";

    //afficher(errorString);
    // Appeler la fonction pour filtrer les fichiers multimédias si selection n est pas tout

    //std::this_thread::sleep_for(std::chrono::milliseconds(5000));
   // buGfileinfoisgood(allFiles,L"filtre remove double ok ");
    
    
}
// remplacé par b





std::vector<unsigned char> CalculateSHA256b(const std::wstring& filePath)
{
    std::ifstream file(filePath, std::ios::binary);
    std::vector<unsigned char> hash(SHA256_DIGEST_LENGTH);

    if (!file)
    {
        //
    }
    

    
    SHA256_CTX sha256;
    SHA256_Init(&sha256);

    const int bufferSize = 32768;
    char buffer[bufferSize];

    while (!file.eof())
    {
      
        file.read(buffer, bufferSize);
        SHA256_Update(&sha256, buffer, file.gcount());
    }

    SHA256_Final(hash.data(), &sha256);

    file.close();

    
    return hash;
    
   
}




// préparation au trie par taille :
// 
bool size_compareFileInfo(const FileInfo& a, const FileInfo& b) {
    // Comparez les tailles de fichier pour le tri (du plus grand au plus petit)
    return a.fileSize > b.fileSize;
}
//////////////////////////////////////////////////////////<< maj
namespace fs = boost::filesystem;

std::wstring FindDuplicatePathsb(const std::vector<std::wstring>& subDirectories) {
    std::unordered_multimap<DWORD, std::wstring> sizeMap;
    std::wstring duplicatesText;


    try {
        bool Odetected=false;
        for (size_t i = 0; i < allFiles.size(); ++i) {
            //afficher(L"\r\n \r\n \r\n <test original " + allFiles[i].filePath+L" , \r\n");
            bool fori = false;
            std::wstring filetoc1 = allFiles[i].filePath;

            std::vector<unsigned char> hash1 = allFiles[i].hash;

            DWORD size1 = allFiles[i].fileSize;

            for (size_t r = 0; r < duplicatePathsO.size(); ++r) {
                if (allFiles[i].filePath == duplicatePathsO[r].filePath) {
                    //if (std::find(duplicatePathsO.begin(), duplicatePathsO.end(), filetoc1) != duplicatePathsO.end()) {

                    fori = true;
                   
                }

             }

          
            if (fori) { 
                //afficher(L" i déjà,detecté " + allFiles[i].filePath+L" /\r\n ");
                continue;//exclure le premier comme original
            }
            fori = false;
            Odetected = false;
              iterationSb += 1;
                _stprintf_s(tailleTotalStrsp, _T("%u candidats"), iterationSb);
                SetWindowText(hwndButtonFilespCount, tailleTotalStrsp);
            for (size_t j = 0; j < allFiles.size(); ++j) {
                //afficher(L" < original " + allFiles[i].filePath + L" , doublon? "+allFiles[j].filePath+L" > \r\n");
                std::wstring filetoc2 = allFiles[j].filePath;
                DWORD size2 = allFiles[j].fileSize;
                std::vector<unsigned char> hash2 = allFiles[j].hash;
                //std::this_thread::sleep_for(std::chrono::milliseconds(10));
                if (iterationSb > iterationS) {
                  //  break;
                }
                for (size_t r = 0; r < duplicatePathsO.size(); ++r) {
                    if (filetoc2 == duplicatePathsO[r].filePath) {
                        //exclure le premier comme original et ne plus le mettre dans le fichier
                        fori = true;

                    }
                }
                for (size_t r = 0; r < duplicatePaths.size(); ++r) {
                    if (allFiles[j].filePath == duplicatePaths[r].filePath)
                    {

                        // Chemin déjà détecté comme doublon, passer au fichier suivant
                        fori = true;
                    }
                }
                if (fori) {
                    fori = false;
                    continue;//exclure le premier comme original
                }
                if (i == j) {
                    continue;  // Chemin déjà détecté comme doublon, passer au fichier suivant oiu lui même
                }
                if (
                    (hash1 == hash2) and (size1 == size2)
                    //CalculateSHA256(filetoc1, filetoc2, hash,hash2)
                    //CompareFilesBySHA1(filetoc1, filetoc2)
                    //CompareFilesByMD5(filetoc1, filetoc2)
                    ) {
                    //afficher(L" < original-Oui " + allFiles[i].filePath + L" , doublon?Oui " + allFiles[j].filePath + L" > \r\n");
                    if (!Odetected) { duplicatePathsO.push_back(allFiles[i]); Odetected = true; }
                
                   
                    
                    duplicatesText += L"\r\n Doublon>(" + std::to_wstring(allFiles[j].fileSize) + L") : " + filetoc2 + L"\r\n";
                    duplicatesText += L"\r\n >>- de > (" + std::to_wstring(allFiles[i].fileSize) + L") : " + filetoc1 + L"\r\n";
                    Taille_total += allFiles[i].fileSize / 1000;
                    nombre_de_Fichiers_total += 1;
                    duplicatePaths.push_back(allFiles[j]);
                }
                          
                                    
                  
                
            }
        }
    }
    catch (const std::exception& ex) {

        std::wstring errorString = L"Erreur lors de la recharche des doublons : ";
        errorString += std::wstring(ex.what(), ex.what() + strlen(ex.what()));
        errorString += L"\r\n";
        duplicatesText += errorString;
        afficher(errorString);
    }

    // Reste de votre code

    _stprintf_s(tailleTotalStr, _T("%u Mo"), Taille_total);

    _stprintf_s(nombreFichiersTotalStr, _T("%u Fichiers"), nombre_de_Fichiers_total);
    return duplicatesText;
}


void chemins_aff() {
    std::wstring allpathSelected = pathSelected;
    allpathSelected += L", ";
    allpathSelected += pathSelected2;
    allpathSelected += L", ";
    allpathSelected += pathSelected3;
    SetWindowText(hwndButton, allpathSelected.c_str());
}
void DeleteLastDuplicateanc() {
    if (!duplicatePaths.empty()) {
        duplicatePaths.pop_back();
    }
}
void DeleteLastDuplicate() {
    if (!duplicatePaths.empty()) {
        // Delete the file from the disk
        if (DeleteFile(duplicatePaths[0].filePath.c_str())) {
            // File deletion successful
            // Remove the path from the list
            duplicatePaths.erase(duplicatePaths.begin());
        }
        else {
            // File deletion failed
            DWORD error = GetLastError();
            // Handle error condition here
            // You can display the error using MessageBox or print it to console
            MessageBox(NULL, (L"Failed to delete file. Error code: " + std::to_wstring(error)).c_str(), L"Error", MB_ICONERROR | MB_OK);
        }
    }
}
void clearTh(){
        for (int itb = 0; itb < allFiles.size(); ++itb) {
            if (!allFiles[itb].hash.empty()) {
                // Le champ hash est vide.

                // Supprimer la thread du vecteur
                threads.erase(threads.begin() + itb);

                // Assurez-vous de décrémenter itb car le vecteur a été réduit en taille
                --itb;
           
                
                }

           
        }
}
void FillFileHashes(std::vector<FileInfo>& allFiles) {
    try {
        int package = 0;
        threads.reserve(allFiles.size());
        itbA = 0;
      
        for (FileInfo& fileInfo : allFiles) {
            itbA++; package++;
            if (package > (200 / 100) * procSeuil) {
                std::this_thread::sleep_for(std::chrono::milliseconds(200)); package = 0;
            }
            std::thread thread([filePath = fileInfo.filePath, &hash = fileInfo.hash]() {
                hash = CalculateSHA256b(filePath);
            });

            thread.detach();
            threads.emplace_back(std::move(thread));
            if (coefProc > procSeuil) {

                while (coefProc > round(procSeuil - 10)) {
                    std::wstring errorString = L"Processeur au dessus du seuil ! de :" + std::to_wstring(procSeuil) + L" pourcent";

                    errorString += L"\r\n";

                    afficher(errorString);
                    std::this_thread::sleep_for(std::chrono::milliseconds(2000));
                }
            }
            if (itbA - itbB > (800 / 100) * procSeuil) {
                //clearTh(); 
                std::wstring errorString = L"Mise en attente pour la lecture des hash selon votre curseur : " + std::to_wstring(procSeuil) + L" pourcent!";

                errorString += L"\r\n";

                afficher(errorString);
                //while (itbA-itbB > (800 / 200) * procSeuil) { std::this_thread::sleep_for(std::chrono::milliseconds(100000)); }
                std::this_thread::sleep_for(std::chrono::milliseconds(2000));
                errorString = L"Reprise de la lectures des Hash";

                errorString += L"\r\n";

                afficher(errorString);
            }


        }

        bool someHashesEmpty = true;  // Initialisation à true pour entrer dans la boucle au moins une fois
                  
            
        while (someHashesEmpty) {
            someHashesEmpty = false;  // Supposons que tous les champs hash soient remplis

            for (int itb = 0; itb < allFiles.size(); ++itb) {
                if (allFiles[itb].hash.empty()) {
                    //MessageBox(NULL, L"Le champ hash est vide.", L"Alerte", MB_ICONWARNING | MB_OK);
                    someHashesEmpty = true;  // Au moins un champ hash est vide
                    if (threads[itb].joinable()) {
                        MessageBox(NULL, L"Le champ hash est vide et la thread non jointe.", L"Alerte", MB_ICONWARNING | MB_OK);
                        threads[itb].detach(); // Joindre la thread correspondante si le hash est vide et la thread est joignable
                    }
                }
            }
        }
        //clearTh();
    }
    catch (const std::exception& e) {
        std::cerr << "Exception attrapée : " << e.what() << std::endl;
        std::wstring errorString = L"Erreur lors de de la lecture des hash256 : ";
        errorString += std::wstring(e.what(), e.what() + strlen(e.what()));
        errorString += L"\r\n";

        afficher(errorString);

    }

    threads.clear();
}

// Fonction de traitement
void ComboFill() {
    SendMessage(hComboBox, CB_RESETCONTENT, 0, 0);
    SendMessage(hComboBox3, CB_RESETCONTENT, 0, 0);
    if (duplicatePaths.size() and duplicatePathsO.size() > 0) {
        // for (const FileInfo& fileInfoA : duplicatePaths) {
        for (size_t i = 0; i < duplicatePathsO.size(); ++i) {
            //SendMessage(hCheckBox1, BM_SETCHECK, (selectedIndex == 0) ? BST_CHECKED : BST_UNCHECKED, 0);

            SendMessage(hComboBox, CB_ADDSTRING, 0, (LPARAM)L"Fichier original :");
            SendMessage(hComboBox, BM_SETCHECK, (selectedIndex == 0) ? BST_CHECKED : BST_UNCHECKED, 0);

            SendMessage(hComboBox, CB_ADDSTRING, 0, (LPARAM)duplicatePathsO[i].filePath.c_str());
            SendMessage(hComboBox, CB_ADDSTRING, 0, (LPARAM)L"Doublon(s) pour le chemin du fichier original :");
            SendMessage(hComboBox3, CB_ADDSTRING, 0, (LPARAM)L"Doublon(s) le quel passer en original?");
            int filesizeo = duplicatePathsO[i].fileSize;
            for (size_t j = 0; j < allFiles.size(); ++j) {

                if (duplicatePaths[j].fileSize == filesizeo) {
                    SendMessage(hComboBox, CB_ADDSTRING, 0, (LPARAM)duplicatePaths[j].filePath.c_str());
                    SendMessage(hComboBox3, CB_ADDSTRING, 0, (LPARAM)duplicatePaths[j].filePath.c_str());
                }
            }
        }
    }

}
void backwinprocess() {
    while (backWin){
        // Votre code d'interprétation ici
        if (searchD) {
            iterationS = 0;// iterations set to 0 cause of exeptiont root var have to use globales
            iterationSb = 0;
            continueLoop = true;
            Taille_total = 0;
            nombre_de_Fichiers_total = 0;
            duplicatePaths.clear();
            duplicatePathsO.clear();
            //buGfileinfoisgood(allFiles, L"before clear allF ");
            allFiles.clear();


            SetWindowText(hwndButtonFileCount, L"Afficher Nombre de fichiers");
            SetWindowText(hwndButtonSizeTotal, L"Afficher Taille totale");
            SetWindowText(hwndButtonFileCount, L"-");
            SetWindowText(hwndButtonFilespCount, L"-");
            //lancer le remplissage recurcif de allfiles path 1 et path 2
            ListSubdirectories(pathSelected);
            while (recursiveCallsCounter.load() > 1) {
                std::this_thread::sleep_for(std::chrono::milliseconds(100));
            }
            ListSubdirectories(pathSelected2);
            while (recursiveCallsCounter.load() > 1) {
                std::this_thread::sleep_for(std::chrono::milliseconds(100));
            }
            while (recursiveCallsCounter.load() > 1) {
                std::this_thread::sleep_for(std::chrono::milliseconds(100));
            }
            ListSubdirectories(pathSelected3);
            while (recursiveCallsCounter.load() > 1) {
                std::this_thread::sleep_for(std::chrono::milliseconds(100));
            }
         
            RemoveNonDuplicates();
            std::sort(allFiles.begin(), allFiles.end(), size_compareFileInfo);
            //buGfileinfoisgood(allFiles, L"before hash allF ");
            FillFileHashes(allFiles);
            //buGfileinfoisgood(allFiles, L"after hash allF ");
            // Ajoutez des éléments à la zone cliquable
            //SendMessage(hComboBox, CB_ADDSTRING, 0, (LPARAM)to_wstring(allFiles[0].filePath));
           // SendMessage(hComboBox, CB_ADDSTRING, 0, (LPARAM)L"Option 2");
            //SendMessage(hComboBox, CB_ADDSTRING, 0, (LPARAM)L"Option 3");

            SetWindowText(hwndButtonFilesCount, tailleTotalStrs);
            // Boucle pour ajouter les chemins de fichier à la ComboBox
            SendMessage(hComboBox, CB_RESETCONTENT, 0, 0);
            SendMessage(hComboBox3, CB_RESETCONTENT, 0, 0);

           
            //std::wstring duplicatesText = pathSelected + FindDuplicatePaths(pathSelected);
           // for (size_t i = 0; i < subDirectories.size(); ++i) {
             //   duplicatesText += subDirectories[i] + L" : " + FindDuplicatePaths(subDirectories[i]);
            //}
            // hEdit = hwndButton;  hEdit
            std::wstring allpathSelected = pathSelected;
            allpathSelected += pathSelected2;   allpathSelected += pathSelected3;
            std::wstring duplicatesText = allpathSelected +FindDuplicatePathsb(subDirectories);
            SendMessageW(hEdit, WM_SETTEXT, 0, (LPARAM)duplicatesText.c_str());
            //buGfileinfoisgood(duplicatePathsO, L" dupli o ");
            //buGfileinfoisgood(duplicatePaths, L"dupli");
            ComboFill();
            ShowWindow(hwndButtonS, SW_HIDE);
            //ShowWindow(hEdit, SW_RESTORE);
            ShowWindow(hwndButtonDelete, SW_RESTORE);
            ShowWindow(hwndButtonIgnore, SW_RESTORE);
            ShowWindow(hwndButtonDeleteAll, SW_RESTORE);

            std::string nbd = " Doublons!";
            std::string tailld = " Mo";
            //tailleTotalStr += nbd.c_str();
            //nombreFichiersTotalStr += tailld.c_str()
            // Mettre à jour le texte des boutons avec les valeurs converties
            SetWindowText(hwndButtonSizeTotal, nombreFichiersTotalStr);
            SetWindowText(hwndButtonFileCount, tailleTotalStr);
            searchD = false;

        }
        // Exemple : Attendez un peu avant la prochaine itération
        std::this_thread::sleep_for(std::chrono::milliseconds(100));
    }
    
}
void backwinprocessTh() {
    while (backWin) {

       
        // Votre code d'interprétation ici
       // Avant d'accéder aux variables partagées, acquérez le mutex.
        WaitForSingleObject(mutex, INFINITE);

        iterationthrd = itbA- itbB;
        _stprintf_s(iterationthrdStr, _T("%u Threads"), iterationthrd);
        SetWindowText(hwndButtonThrdCount, iterationthrdStr);

        // Après avoir modifié les variables, libérez le mutex.
        ReleaseMutex(mutex);
        int nbjoin = 0;
        itbB = 0;
        for (int itb = 0; itb < allFiles.size(); ++itb) {
            if (allFiles[itb].hash.empty()) {
           
                nbjoin++;
            }
            else
            {
                itbB++;
            }
        }
        WaitForSingleObject(mutex, INFINITE);
        int charged = itbA - itbB;
        iterationthrdb = nbjoin;//nbjoin;
        _stprintf_s(iterationthrdStrb, _T("%u non lus totalement"), iterationthrdb);
        SetWindowText(hwndButtonThrdCountb, iterationthrdStrb);
        GetcoefProc();
        int roundedValue = static_cast<int>(std::round((GetSystemCpuUsage())*1));
        if (roundedValue > 100) { roundedValue = 99; }
        coefProc = roundedValue;
         
        _stprintf_s(coefProcS, _T("%u pourcent of CPU"), roundedValue);
          SetWindowText(hwndButtonproc, coefProcS);
          // Récupérer la position actuelle du slider
          procSeuil = SendMessage(hSlider, TBM_GETPOS, 0, 0);

          // Mettre à jour le texte du bouton avec la position actuelle du slider
          std::wstring buttonText = L"Seuil limite pourcent: " + std::to_wstring(procSeuil) + L"%";
          SetWindowText(hwndButtonprocS, buttonText.c_str());
        // Après avoir modifié les variables, libérez le mutex.
        ReleaseMutex(mutex);
        std::this_thread::sleep_for(std::chrono::milliseconds(1000));
    }

}
// Function to ignore the last duplicate path from the list
void IgnoreLastDuplicate() {
    if (!duplicatePaths.empty()) {
        duplicatePaths.erase(duplicatePaths.begin());
    }
}

void UpdateDuplicatePathsInEditControl(HWND hEdit, const std::vector<FileInfo>& paths) {
    std::wstring updatedText;
   // for (const std::wstring& path : paths) {
    for (size_t i = 0; i < duplicatePaths.size(); ++i) {
        updatedText += paths[i].filePath + L"\r\n";
    }
    SetWindowText(hEdit, updatedText.c_str());
}



int APIENTRY wWinMain(_In_ HINSTANCE hInstance,
    _In_opt_ HINSTANCE hPrevInstance,
    _In_ LPWSTR    lpCmdLine,
    _In_ int       nCmdShow)
{
    UNREFERENCED_PARAMETER(hPrevInstance);
    UNREFERENCED_PARAMETER(lpCmdLine);

    LoadStringW(hInstance, IDS_APP_TITLE, szTitle, MAX_LOADSTRING);
    LoadStringW(hInstance, IDC_WINDOWSPROJECT3, szWindowClass, MAX_LOADSTRING);
    MyRegisterClass(hInstance);
    try {
    std::thread processingThread(backwinprocess);
    std::thread processingThread2(backwinprocessTh);
    processingThread.detach();
    processingThread2.detach();
    }
    catch (const std::exception& e) {
        std::cerr << "Exception attrapée : " << e.what() << std::endl;
        std::wstring errorString = L"Erreur lors de lancement des taches de fond: back et thread iterator ";
        errorString += std::wstring(e.what(), e.what() + strlen(e.what()));
        errorString += L"\r\n";

        afficher(errorString);
    }
    if (!InitInstance(hInstance, nCmdShow))
    {
        return FALSE;
    }
   


   
    HACCEL hAccelTable = LoadAccelerators(hInstance, MAKEINTRESOURCE(IDC_WINDOWSPROJECT3));

    MSG msg;

    while (GetMessage(&msg, nullptr, 0, 0))
    {
        if (!TranslateAccelerator(msg.hwnd, hAccelTable, &msg))
        {
            TranslateMessage(&msg);
            DispatchMessage(&msg);
        }
    }
    
    return (int)msg.wParam;
}
LRESULT CALLBACK WindowProc(HWND hwnd, UINT uMsg, WPARAM wParam, LPARAM lParam) {
    switch (uMsg) {
    case WM_CLOSE:
        // Gérer la fermeture de la fenêtre ici
        // Arrêtez vos threads en cours si nécessaire
        // Puis fermez la fenêtre avec DestroyWindow(hwnd) ou PostQuitMessage(0)
        break;
        // Autres messages et gestionnaires ici
    }
    return DefWindowProc(hwnd, uMsg, wParam, lParam);
  
}
ATOM MyRegisterClass(HINSTANCE hInstance)
{
    WNDCLASSEXW wcex;

    wcex.cbSize = sizeof(WNDCLASSEX);

    wcex.style = CS_HREDRAW | CS_VREDRAW;
    wcex.lpfnWndProc = WndProc;
    wcex.cbClsExtra = 0;
    wcex.cbWndExtra = 0;
    wcex.hInstance = hInstance;
    wcex.hIcon = LoadIcon(hInstance, MAKEINTRESOURCE(IDI_WINDOWSPROJECT3));
    wcex.hCursor = LoadCursor(nullptr, IDC_ARROW);
    wcex.hbrBackground = (HBRUSH)(COLOR_WINDOW + 1);
    wcex.lpszMenuName = MAKEINTRESOURCEW(IDC_WINDOWSPROJECT3);
    wcex.lpszClassName = szWindowClass;
    wcex.hIconSm = LoadIcon(wcex.hInstance, MAKEINTRESOURCE(IDI_SMALL));

    return RegisterClassExW(&wcex);
}



BOOL InitInstance(HINSTANCE hInstance, int nCmdShow)
{
    hInst = hInstance;

    HWND hWnd = CreateWindowW(szWindowClass, szTitle, WS_OVERLAPPEDWINDOW,
        CW_USEDEFAULT, 0, CW_USEDEFAULT, 0, nullptr, nullptr, hInstance, nullptr);

    if (!hWnd)
    {
        return FALSE;
    }

    ShowWindow(hWnd, nCmdShow);
    UpdateWindow(hWnd);

    return TRUE;
}

LRESULT CALLBACK WndProc(HWND hWnd, UINT message, WPARAM wParam, LPARAM lParam)
{


    switch (message)
    {
    case WM_CREATE:
        // Création du bouton "Afficher Taille totale"
        hwndButtonSizeTotal = CreateWindow(
            L"BUTTON",
            L"Afficher Taille totale",
            WS_TABSTOP | WS_VISIBLE | WS_CHILD | BS_DEFPUSHBUTTON,
            710,      // x position
            90,      // y position (position en dessous du bouton "Rechercher les doublons")
            200,     // Button width
            30,      // Button height
            hWnd,
            (HMENU)IDC_BUTTON_SIZE_TOTAL,
            hInst,
            NULL);
     
        // Création du bouton "Afficher Nombre de fichiers"
        hwndButtonFileCount = CreateWindow(
            L"BUTTON",
            L"Afficher Nombre de fichiers",
            WS_TABSTOP | WS_VISIBLE | WS_CHILD | BS_DEFPUSHBUTTON,
            710,      // x position
            130,     // y position (position en dessous du bouton "Afficher Taille totale")
            200,     // Button width
            30,      // Button height
            hWnd,
            (HMENU)IDC_BUTTON_FILE_COUNT,
            hInst,
            NULL);
        // Création du bouton "Afficher Nombre de fichiers scans"
        hwndButtonFilesCount = CreateWindow(
            L"BUTTON",
            L"Afficher Nombre de scans",
            WS_TABSTOP | WS_VISIBLE | WS_CHILD | BS_DEFPUSHBUTTON,
            710,      // x position
            170,     // y position (position en dessous du bouton "Afficher Taille totale")
            200,     // Button width
            30,      // Button height
            hWnd,
            (HMENU)IDC_BUTTON_FILE_COUNT,
            hInst,
            NULL);
        // Création du bouton "Afficher Nombre de threads"
        hwndButtonThrdCount = CreateWindow(
            L"BUTTON",
            L"Afficher Nombre de threads",
            WS_TABSTOP | WS_VISIBLE | WS_CHILD | BS_DEFPUSHBUTTON,
            710,      // x position
            250,     // y position (position en dessous du bouton "Afficher Taille totale")
            200,     // Button width
            30,      // Button height
            hWnd,
            (HMENU)IDC_BUTTON_FILE_COUNT,
            hInst,
            NULL);

        // Création du bouton "Afficher Nombre de threads actives"
        hwndButtonThrdCountb = CreateWindow(
            L"BUTTON",
            L"Afficher threads actives",
            WS_TABSTOP | WS_VISIBLE | WS_CHILD | BS_DEFPUSHBUTTON,
            710,      // x position
            290,     // y position (position en dessous du bouton "Afficher Taille totale")
            200,     // Button width
            30,      // Button height
            hWnd,
            (HMENU)IDC_BUTTON_FILE_COUNT,
            hInst,
            NULL);
        //slider
         hSlider = CreateWindowEx(
             0,
             TRACKBAR_CLASS,
             NULL,
             WS_CHILD | WS_VISIBLE | TBS_AUTOTICKS | TBS_ENABLESELRANGE, 
            
             710, 340, 200, 30,
             hWnd,
            (HMENU)IDC_SLIDER,
            GetModuleHandle(NULL),
            NULL);

        // Définir la plage de valeurs pour le slider (20 à 80)
        SendMessage(hSlider, TBM_SETRANGE, TRUE, MAKELPARAM(20, 80));

        // Définir la valeur initiale du slider
        SendMessage(hSlider, TBM_SETPOS, TRUE, 20);

        
        // Création du bouton "Afficher Nombre de fichiers scans progression"
       // static HWND hwndButtonproc = NULL;
          // Création du bouton "Afficher Nombre de threads actives"
        hwndButtonproc = CreateWindow(
            L"BUTTON",
            L"Afficher processus",
            WS_TABSTOP | WS_VISIBLE | WS_CHILD | BS_DEFPUSHBUTTON,
            710,      // x position
            390,     // y position (position en dessous du bouton "Afficher Taille totale")
            200,     // Button width
            30,      // Button height
            hWnd,
            (HMENU)IDC_BUTTON_FILE_COUNT,
            hInst,
            NULL);
        // Création du bouton "Afficher Nombre de threads actives"
        hwndButtonprocS = CreateWindow(
            L"BUTTON",
            L"Afficher processus",
            WS_TABSTOP | WS_VISIBLE | WS_CHILD | BS_DEFPUSHBUTTON,
            710,      // x position
            440,     // y position (position en dessous du bouton "Afficher Taille totale")
            200,     // Button width
            30,      // Button height
            hWnd,
            (HMENU)IDC_BUTTON_FILE_COUNT,
            hInst,
            NULL);


        hwndButtonFilespCount = CreateWindow(
            L"BUTTON",
            L"Progression pour les scans",
            WS_TABSTOP | WS_VISIBLE | WS_CHILD | BS_DEFPUSHBUTTON,
            710,      // x position
            210,     // y position (position en dessous du bouton "Afficher Taille totale")
            200,     // Button width
            30,      // Button height
            hWnd,
            (HMENU)IDC_BUTTON_FILE_COUNT,
            hInst,
            NULL);
        // Créer le bouton de recherche de doublons
        hwndButtonS = CreateWindow(
            L"BUTTON",
            L"Rechercher!",
            WS_TABSTOP | WS_VISIBLE | WS_CHILD | BS_DEFPUSHBUTTON,
            10,                      // x position
            50,                      // y position (position en dessous du bouton précédent)
            100,                     // Button width
            30,                      // Button height
            hWnd,
            (HMENU)IDM_SEARCH_DUPLICATES,
            hInst,
            NULL);
        // Créer le bouton ici lors de la création de la fenêtre
        // ShowWindow(GetDlgItem(HWND, IDM_SEARCH_DUPLICATES), WS_VISIBLE); WS_VISIBLE

        hwndButton = CreateWindow(
            L"BUTTON",               // Predefined class; Unicode assumed 
            L"Selection du dossier + un ou deux autres!  cliquez sur les + ici               ---------------->",        // Button text 
            WS_TABSTOP | WS_VISIBLE | WS_CHILD | BS_DEFPUSHBUTTON,  // Styles 
            10,                      // x position 
            10,                      // y position 
            850,                     // Button width
            30,                      // Button height
            hWnd,                    // Parent window
            (HMENU)IDM_SELECT_FOLDER, // Utilisez l'identifiant comme menu ID
            hInst,                   // HINSTANCE
            NULL);                   // Pointer not needed.
        hwndButton2 = CreateWindow(
            L"BUTTON",               // Predefined class; Unicode assumed 
            L"+",        // Button text 
            WS_TABSTOP | WS_VISIBLE | WS_CHILD | BS_DEFPUSHBUTTON,  // Styles 
            865,                      // x position 
            10,                      // y position 
            12,                     // Button width
            30,                      // Button height
            hWnd,                    // Parent window
            (HMENU)IDM_SELECT_FOLDER2, // Utilisez l'identifiant comme menu ID
            hInst,                   // HINSTANCE
            NULL);                   // Pointer not needed.
        hwndButton3 = CreateWindow(
            L"BUTTON",               // Predefined class; Unicode assumed 
            L"+",        // Button text 
            WS_TABSTOP | WS_VISIBLE | WS_CHILD | BS_DEFPUSHBUTTON,  // Styles 
            880,                      // x position 
            10,                      // y position 
            12,                     // Button width
            30,                      // Button height
            hWnd,                    // Parent window
            (HMENU)IDM_SELECT_FOLDER3, // Utilisez l'identifiant comme menu ID
            hInst,                   // HINSTANCE
            NULL);                   // Pointer not needed.
        ShowWindow(hwndButtonS, SW_HIDE);
        // Crée la zone de texte
        hEdit = CreateWindowEx(
            0,
            L"EDIT",
            NULL,
            WS_CHILD | WS_VISIBLE | WS_VSCROLL | ES_MULTILINE | ES_AUTOVSCROLL,
            10,
            100,
            690,
            300,
            hWnd,
            (HMENU)IDC_EDIT,
            GetModuleHandle(NULL),
            NULL
        );
   
        //ShowWindow(hEdit, SW_HIDE);
        hComboBox = CreateWindow(
            L"COMBOBOX",
            L"", // Texte initial de la zone cliquable
            WS_CHILD | WS_VISIBLE | CBS_DROPDOWN | CBS_HASSTRINGS | CBS_DISABLENOSCROLL  | LBS_STANDARD | LBS_EXTENDEDSEL| CBS_DROPDOWNLIST,
            130,
            43,
            770, // Largeur de la zone cliquable
            300,
            hWnd,
            (HMENU)IDC_COMBO,
            GetModuleHandle(NULL),
            NULL
        );
        hComboBox3 = CreateWindow(
            L"COMBOBOX",
            L"", // Texte initial de la zone cliquable
            WS_CHILD | WS_VISIBLE | CBS_DROPDOWN | CBS_HASSTRINGS | CBS_DISABLENOSCROLL | LBS_STANDARD | LBS_EXTENDEDSEL,
            130,
            65,
            770, // Largeur de la zone cliquable
            300,
            hWnd,
            (HMENU)IDC_COMBO3,
            GetModuleHandle(NULL),
            NULL
        );
        hComboBox2 = CreateWindow(
            L"COMBOBOX",
            L"", // Texte initial de la zone cliquable
            WS_CHILD | WS_VISIBLE | CBS_DROPDOWN | CBS_HASSTRINGS | CBS_DISABLENOSCROLL | LBS_STANDARD | LBS_EXTENDEDSEL,
            20,
            480,
            850, // Largeur de la zone cliquable
            300,
            hWnd,
            (HMENU)IDC_COMBO2,
            GetModuleHandle(NULL),
            NULL
        );
        SendMessage(hComboBox2, CB_ADDSTRING, 0, (LPARAM)L"Option fichiers : MULTIMEDIA");
        SendMessage(hComboBox2, CB_ADDSTRING, 0, (LPARAM)L"Option fichiers : MULTIMEDIA et Documents");
        SendMessage(hComboBox2, CB_ADDSTRING, 0, (LPARAM)L"Option fichiers : Tout");
       // FillSelectS();
        // Utilisez le message CB_SETCURSEL pour sélectionner l'option
        SendMessage(hComboBox2, CB_SETCURSEL, static_cast<WPARAM>(0), 0);
        // Create the "Delete" button
        hwndButtonDelete = CreateWindow(
            L"BUTTON",
            L"Supprimer",
            WS_TABSTOP | WS_VISIBLE | WS_CHILD | BS_DEFPUSHBUTTON,
            10,                      // x position
            440,                      // y position (position below the "Rechercher les doublons" button)
            100,                     // Button width
            30,                      // Button height
            hWnd,
            (HMENU)IDM_DELETE_DUPLICATE,
            hInst,
            NULL);
        ShowWindow(hwndButtonDelete, SW_HIDE);
        // Create the "Ignore" button
        hwndButtonIgnore = CreateWindow(
            L"BUTTON",
            L"Ignorer",
            WS_TABSTOP | WS_VISIBLE | WS_CHILD | BS_DEFPUSHBUTTON,
            120,                     // x position
            440,                      // y position (position to the right of the "Delete" button)
            100,                     // Button width
            30,                      // Button height
            hWnd,
            (HMENU)IDM_IGNORE_DUPLICATE,
            hInst,
            NULL);
        ShowWindow(hwndButtonIgnore, SW_HIDE);
        hwndButtonDeleteAll = CreateWindow(
            L"BUTTON",
            L"Supprimer tout",
            WS_TABSTOP | WS_VISIBLE | WS_CHILD | BS_DEFPUSHBUTTON,
            530,                     // x position
            440,                     // y position (position to the right of the "Ignore" button)
            100,                     // Button width
            30,                      // Button height
            hWnd,
            (HMENU)IDM_DELETE_ALL_DUPLICATES,
            hInst,
            NULL);
        ShowWindow(hwndButtonDeleteAll, SW_HIDE);
        break;

    case WM_COMMAND:
    {
        if (HIWORD(wParam) == CBN_SELCHANGE) {
            // La sélection dans la ComboBox a changé, réagir ici.
          
            /////////////////// if combo change 
           // Obtenir l'index de l'élément sélectionné
           // 
            //void ComboFill()
            // SwapDuplicatePaths(std::wstring& inputPath) 
            selectedIndexAnc = selectedIndex;
            selectedIndex = SendMessage(hComboBox, CB_GETCURSEL, 0, 0);
            int selectedIndex3 = SendMessage(hComboBox3, CB_GETCURSEL, 0, 0);

            // _wsystem(L"D:\doublontest\1\2\3\Nouveau Classeur OpenDocument - Copie(2).ods");
            if (selectedIndex3 != CB_ERR) {
              
                    // Récupérez le chemin du fichier sélectionné
                    WCHAR selectedFilePath[MAX_PATH];
                    SendMessage(hComboBox3, CB_GETLBTEXT, selectedIndex3, (LPARAM)selectedFilePath);

                    std::wstring pathcombo3 =std::wstring(selectedFilePath).c_str();
                  //  buGfileinfoisgood(duplicatePathsO, L"lol1");
                    SwapDuplicatePaths(pathcombo3);
                        ComboFill();
                     
                       // buGfileinfoisgood(duplicatePathsO, L"lol2");
                 
                
            }
            //////////////////////// si combo3 change
            if (selectedIndex != CB_ERR) {
                if (selectedIndexAnc != selectedIndex) {
                    // Récupérez le chemin du fichier sélectionné
                    WCHAR selectedFilePath[MAX_PATH];
                    SendMessage(hComboBox, CB_GETLBTEXT, selectedIndex, (LPARAM)selectedFilePath);

                    std::wstring path3to = doublerBackslashes(std::wstring(selectedFilePath)).c_str();
                    path3to = L"start \"\" \"" + path3to;
                    path3to += L"\"";

                    //MessageBoxW(NULL, path3to.c_str(), L"Résultat", MB_ICONINFORMATION);
                    int result = _wsystem(path3to.c_str());

                    //  int result = _wsystem(L"start \"\" \"D:\\xampp\\htdocs\\FabVid\\sample.mp4\"");  << echaper les \ et " car utilisé sous system

                    if (result != 0) {
                        // Gérez les erreurs éventuelles ici
                    }
                }
            }
            //////////////////////// si combo2 change


            /////////////////// if combo2 change 
           // Obtenir l'index de l'élément sélectionné

            selectedIndexAnc2 = selectedIndex2;
            selectedIndex2 = SendMessage(hComboBox2, CB_GETCURSEL, 0, 0);

          

            if (selectedIndex2 != CB_ERR) {
               
               
            }
            //////////////////////// si combo2 change
        }
        int wmId = LOWORD(wParam);
        switch (wmId)
        {
        case IDM_SELECT_FOLDER:
        {
            subDirectories.clear();
            continueLoop = true;
            BROWSEINFO bi = { 0 };
            bi.lpszTitle = L"Select a folder";
            LPITEMIDLIST pidl = SHBrowseForFolder(&bi);
            duplicatesTexterr = L"";
            if (pidl != NULL)
            {

                //ShowWindow(hEdit, SW_HIDE);
                ShowWindow(hwndButtonDelete, SW_HIDE);
                ShowWindow(hwndButtonIgnore, SW_HIDE);
                ShowWindow(hwndButtonDeleteAll, SW_HIDE);
                TCHAR selectedPath[MAX_PATH];
                if (SHGetPathFromIDList(pidl, selectedPath))
                {
                    // Copier le chemin sélectionné dans pathSelected
                    lstrcpy(pathSelected, selectedPath);
                    InvalidateRect(hWnd, NULL, TRUE);
                    // Mettre à jour le texte du bouton avec le chemin sélectionné
                    chemins_aff();

                    ShowWindow(hwndButtonS, SW_RESTORE);
                    WM_PAINT;
                }

                IMalloc* pMalloc;
                if (SUCCEEDED(SHGetMalloc(&pMalloc)))
                {
                    pMalloc->Free(pidl);
                    pMalloc->Release();
                }
            }
        }
        break;
        case IDM_SELECT_FOLDER2:
        {
            subDirectories.clear();
            continueLoop = true;
            BROWSEINFO bi = { 0 };
            bi.lpszTitle = L"Select a folder";
            LPITEMIDLIST pidl = SHBrowseForFolder(&bi);
            duplicatesTexterr = L"";
            if (pidl != NULL)
            {

                //ShowWindow(hEdit, SW_HIDE);
                ShowWindow(hwndButtonDelete, SW_HIDE);
                ShowWindow(hwndButtonIgnore, SW_HIDE);
                ShowWindow(hwndButtonDeleteAll, SW_HIDE);
                TCHAR selectedPath[MAX_PATH];
                if (SHGetPathFromIDList(pidl, selectedPath))
                {
                    // Copier le chemin sélectionné dans pathSelected
                    lstrcpy(pathSelected2, selectedPath);
                    InvalidateRect(hWnd, NULL, TRUE);
                    // Mettre à jour le texte du bouton avec le chemin sélectionné
                    chemins_aff();

                    ShowWindow(hwndButtonS, SW_RESTORE);
                    WM_PAINT;
                }

                IMalloc* pMalloc;
                if (SUCCEEDED(SHGetMalloc(&pMalloc)))
                {
                    pMalloc->Free(pidl);
                    pMalloc->Release();
                }
            }
        }
        break;
        case IDM_SELECT_FOLDER3:
        {
            subDirectories.clear();
            continueLoop = true;
            BROWSEINFO bi = { 0 };
            bi.lpszTitle = L"Select a folder";
            LPITEMIDLIST pidl = SHBrowseForFolder(&bi);
            duplicatesTexterr = L"";
            if (pidl != NULL)
            {

                //ShowWindow(hEdit, SW_HIDE);
                ShowWindow(hwndButtonDelete, SW_HIDE);
                ShowWindow(hwndButtonIgnore, SW_HIDE);
                ShowWindow(hwndButtonDeleteAll, SW_HIDE);
                TCHAR selectedPath[MAX_PATH];
                if (SHGetPathFromIDList(pidl, selectedPath))
                {
                    // Copier le chemin sélectionné dans pathSelected
                
                    lstrcpy(pathSelected3, selectedPath);                    InvalidateRect(hWnd, NULL, TRUE);
                    // Mettre à jour le texte du bouton avec le chemin sélectionné
                    chemins_aff();
                

                    ShowWindow(hwndButtonS, SW_RESTORE);
                    WM_PAINT;
                }

                IMalloc* pMalloc;
                if (SUCCEEDED(SHGetMalloc(&pMalloc)))
                {
                    pMalloc->Free(pidl);
                    pMalloc->Release();
                }
            }
        }
        break;
        case IDM_SEARCH_DUPLICATES:
        {
            if (!searchD) {
                searchD = true;
            }

        }
        break;

        // Handle the "Delete" button click
        case IDM_DELETE_DUPLICATE:
        {
            DeleteLastDuplicate();
            // Update the text in the edit control with the modified duplicate paths
            UpdateDuplicatePathsInEditControl(hEdit, duplicatePaths);

        }
        break;

        // Handle the "Ignore" button click
        case IDM_IGNORE_DUPLICATE:
        {
           // int retourtest=system("start \"\" \"D:\\xampp\\htdocs\\FabVid\\sample.mp4\"");
            
        
        IgnoreLastDuplicate();
       // if (retourtest != 0) { }
            // Update the text in the edit control with the modified duplicate paths
            UpdateDuplicatePathsInEditControl(hEdit, duplicatePaths);
        
        
        }

        break;
        case IDM_DELETE_ALL_DUPLICATES:
        { 
            for (size_t i = 0; i < duplicatePaths.size(); ++i) {
            //for (const std::wstring& filePath : duplicatePaths.filePath) {
                if (DeleteFile(duplicatePaths[i].filePath.c_str())) {
                    // File deletion successful, do something if needed
                }
                else {
                    // File deletion failed, handle the error
                }
            }
            // Clear the duplicatePaths vector
            duplicatePaths.clear();
            duplicatePathsO.clear();
            // Clear the edit control
            SetWindowText(hEdit, L"");
            // Hide the "Supprimer tout" button
            ShowWindow(hwndButtonDeleteAll, SW_HIDE);
        }
        break;
        case CBN_SELCHANGE: {// lparam ne change pas là

        }
        case WM_HSCROLL:
        {
            // ne marche pas: >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> ! >>>>>>>>>>>>>>>>
            // Récupérer la position actuelle du slider 
            procSeuil = SendMessage(hSlider, TBM_GETPOS, 0, 0);

            // Mettre à jour le texte du bouton avec la position actuelle du slider
            std::wstring buttonText = L"Seuil limite pourcent: " + std::to_wstring(procSeuil) + L"%";
            SetWindowText(hwndButtonprocS, buttonText.c_str());
        }
        break;

                
        
           
        case IDM_ABOUT:
            DialogBox(hInst, MAKEINTRESOURCE(IDD_ABOUTBOX), hWnd, About);
            break;
        case IDM_EXIT:
            DestroyWindow(hWnd);
            break;
        default:
            return DefWindowProc(hWnd, message, wParam, lParam);
        }
        
    }
    break;
    case WM_PAINT:
    {
        PAINTSTRUCT ps;
        HDC hdc = BeginPaint(hWnd, &ps);
        EndPaint(hWnd, &ps);
    }
    break;

    case WM_DESTROY:
        PostQuitMessage(0);
        break;
    default:
        return DefWindowProc(hWnd, message, wParam, lParam);
    }

    return 0;
}

INT_PTR CALLBACK About(HWND hDlg, UINT message, WPARAM wParam, LPARAM lParam)
{
    UNREFERENCED_PARAMETER(lParam);
    switch (message)
    {
    case WM_INITDIALOG:
        return (INT_PTR)TRUE;
    case WM_COMMAND:
        if (LOWORD(wParam) == IDOK || LOWORD(wParam) == IDCANCEL)
        {
            EndDialog(hDlg, LOWORD(wParam));
            return (INT_PTR)TRUE;
        }
        break;
    }
    return (INT_PTR)FALSE;
}