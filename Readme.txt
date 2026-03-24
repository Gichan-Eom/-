로컬 기준.
채팅기능 사용 시, Composer(PHP 패키지 관리 도구) 필요
Composer 다운로드 링크:  https://getcomposer.org/download/

설치 프로그램이 자동으로 PHP 경로를 찾아서 설정해주고, 
명령 프롬프트에서 composer 명령어를 사용할 수 있게 해줌
PATH에서 환경변수 확인 가능) C:\Users\user\AppData\Roaming\Composer\vendor\bin

vsCode 터미널
composer --version 입력 시, 버전 정보가 나와야함
예시)
PS C:\xampp\htdocs\project\chatPage> composer --version
Composer version 2.8.9 2025-05-13 14:01:37
PHP version 8.2.12 (C:\xampp\php\php.exe)
Run the "diagnose" command to get more detailed diagnostics output.

로컬 쳇서버(chatServer.php) 실행 방법

1)시스템 환경변수 PATH에 PHP환경변수가 설정되어 있는 경우) C:\xampp\php
vsCode터미널
 C:\xampp\htdocs\project\chatPage로 이동 -> php chatServer.php  명령어 실행

Chat 서버 객체 생성
서버 시작 (0.0.0.0:8080) 

2)터미널 없이 XAMPP로 실행
http://localhost/project/chatPage/chatRoom.php 주소로 실행(계속 로딩 상태여야 함.)

그 후 새 페이지로 project에 있는 페이지 접속

